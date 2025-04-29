"""
Agent Orchestration System

This module provides the orchestration system for Teko agents.
It manages agent scheduling, priority management, and resource allocation.
"""

import logging
import queue
import threading
import time
from typing import Any, Dict, Optional, Type

from agents.core.base_agent import BaseAgent

# Configure logging
logging.basicConfig(
    level=logging.INFO, format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)


class TaskQueue:
    """
    A priority queue for agent tasks with different priority levels.
    """

    def __init__(self):
        """Initialize the task queues with different priority levels."""
        self.high_priority: queue.Queue[Dict[str, Any]] = queue.Queue()
        self.medium_priority: queue.Queue[Dict[str, Any]] = queue.Queue()
        self.low_priority: queue.Queue[Dict[str, Any]] = queue.Queue()
        self.logger = logging.getLogger("teko.orchestrator.queue")

    def add_task(self, task: Dict[str, Any], priority: str = "medium") -> None:
        """
        Add a task to the queue with specified priority.

        Args:
            task: The task to add
            priority: Priority level (high, medium, low)
        """
        if priority == "high":
            self.high_priority.put(task)
        elif priority == "low":
            self.low_priority.put(task)
        else:  # Default to medium
            self.medium_priority.put(task)

        self.logger.info(f"Added task {task.get('id', 'unknown')} with {priority} priority")

    def get_next_task(self) -> Optional[Dict[str, Any]]:
        """
        Get the next task from the highest priority queue that has tasks.

        Returns:
            The next task or None if all queues are empty
        """
        try:
            # Check queues in priority order
            if not self.high_priority.empty():
                return self.high_priority.get_nowait()

            if not self.medium_priority.empty():
                return self.medium_priority.get_nowait()

            if not self.low_priority.empty():
                return self.low_priority.get_nowait()

            return None
        except queue.Empty:
            return None


class Orchestrator:
    """
    Agent orchestration system that coordinates and schedules agent tasks.
    """

    def __init__(self):
        """Initialize the orchestrator with empty agent and task registries."""
        self.agents: Dict[str, BaseAgent] = {}
        self.agent_classes: Dict[str, Type[BaseAgent]] = {}
        self.task_queue = TaskQueue()
        self.running = False
        self.worker_thread = None
        self.logger = logging.getLogger("teko.orchestrator")

        # Stats and monitoring
        self.tasks_processed = 0
        self.tasks_succeeded = 0
        self.tasks_failed = 0

    def register_agent_class(self, agent_type: str, agent_class: Type[BaseAgent]) -> None:
        """
        Register an agent class for a specific agent type.

        Args:
            agent_type: The type of agent (codebase_analysis, implementation, etc.)
            agent_class: The agent class to register
        """
        self.agent_classes[agent_type] = agent_class
        self.logger.info(f"Registered {agent_class.__name__} for agent type '{agent_type}'")

    def create_agent(
        self, agent_type: str, name: str, config: Optional[Dict[str, Any]] = None
    ) -> Optional[BaseAgent]:
        """
        Create a new agent of the specified type.

        Args:
            agent_type: The type of agent to create
            name: The name for the new agent
            config: Optional configuration for the agent

        Returns:
            The created agent or None if agent_type is not registered
        """
        if agent_type not in self.agent_classes:
            self.logger.error(f"No agent class registered for type '{agent_type}'")
            return None

        agent_class = self.agent_classes[agent_type]
        agent = agent_class(name=name, agent_type=agent_type, config=config)
        self.agents[name] = agent

        self.logger.info(f"Created agent '{name}' of type '{agent_type}'")
        return agent

    def add_task(self, task: Dict[str, Any], priority: str = "medium") -> None:
        """
        Add a task to be processed by an appropriate agent.

        Args:
            task: The task data dictionary
            priority: The priority level for the task (high, medium, low)
        """
        # Add some metadata to the task
        task["added_time"] = time.time()
        task["status"] = "pending"

        # Add to the queue
        self.task_queue.add_task(task, priority=priority)

    def _process_tasks(self) -> None:
        """Process tasks from the queue in a worker thread."""
        self.logger.info("Task processing started")

        while self.running:
            task = self.task_queue.get_next_task()

            if task is None:
                # No tasks in queue, sleep briefly
                time.sleep(1)
                continue

            # Find appropriate agent for the task
            agent = self._find_agent_for_task(task)

            if agent is None:
                self.logger.warning(f"No suitable agent found for task {task.get('id', 'unknown')}")
                # Re-queue with lower priority or log failure
                task["status"] = "agent_not_found"
                self.tasks_failed += 1
                continue

            # Update task status
            task["status"] = "processing"
            task["agent"] = agent.name
            task["start_time"] = time.time()

            try:
                # Process the task
                self.logger.info(
                    f"Agent '{agent.name}' processing task {task.get('id', 'unknown')}"
                )
                result = agent.process_task(task)

                # Update task with result
                task["status"] = "completed"
                task["complete_time"] = time.time()
                task["result"] = result

                self.tasks_processed += 1
                self.tasks_succeeded += 1

                self.logger.info(f"Task {task.get('id', 'unknown')} completed successfully")
            except Exception as e:
                # Handle task failure
                self.logger.error(f"Error processing task {task.get('id', 'unknown')}: {str(e)}")
                task["status"] = "failed"
                task["error"] = str(e)
                task["complete_time"] = time.time()

                self.tasks_processed += 1
                self.tasks_failed += 1

                # Log error in the agent
                agent.log_error(e, context={"task": task})

    def _find_agent_for_task(self, task: Dict[str, Any]) -> Optional[BaseAgent]:
        """
        Find an appropriate agent to handle the given task.

        Args:
            task: The task to find an agent for

        Returns:
            An agent that can handle the task or None if no suitable agent found
        """
        # If task specifies a specific agent, use that
        if "agent_name" in task and task["agent_name"] in self.agents:
            return self.agents[task["agent_name"]]

        # Otherwise, find any agent that can handle this task
        for agent in self.agents.values():
            if agent.can_handle_task(task):
                return agent

        return None

    def start(self) -> None:
        """Start the orchestrator's task processing thread."""
        if self.running:
            self.logger.warning("Orchestrator is already running")
            return

        self.running = True
        self.worker_thread = threading.Thread(target=self._process_tasks)
        self.worker_thread.daemon = True
        self.worker_thread.start()

        self.logger.info("Orchestrator started")

    def stop(self) -> None:
        """Stop the orchestrator's task processing thread."""
        if not self.running:
            self.logger.warning("Orchestrator is not running")
            return

        self.running = False
        if self.worker_thread:
            self.worker_thread.join(timeout=5.0)

        self.logger.info("Orchestrator stopped")

    def get_stats(self) -> Dict[str, Any]:
        """
        Get orchestrator statistics.

        Returns:
            Dictionary of statistics
        """
        return {
            "tasks_processed": self.tasks_processed,
            "tasks_succeeded": self.tasks_succeeded,
            "tasks_failed": self.tasks_failed,
            "registered_agents": len(self.agents),
            "agent_types": list(self.agent_classes.keys()),
            "running": self.running,
        }
