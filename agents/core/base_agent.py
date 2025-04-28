"""
Base Agent Class for Teko

This module defines the BaseAgent class that all Teko agents will inherit from.
It provides common functionality and interfaces for agent communication,
error handling, and task processing.
"""

import abc
import json
import logging
import datetime
from typing import Dict, List, Any, Optional

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)


class BaseAgent(abc.ABC):
    """
    Base class for all Teko agents. Provides common functionality and interfaces.
    
    All specialized agents should inherit from this class and implement
    the required abstract methods.
    """
    
    def __init__(self, name: str, agent_type: str, config: Dict[str, Any] = None):
        """
        Initialize a new agent.
        
        Args:
            name: The name of the agent
            agent_type: The type of agent (implementation, review, story_management, etc.)
            config: Optional configuration dictionary
        """
        self.name = name
        self.agent_type = agent_type
        self.config = config or {}
        self.logger = logging.getLogger(f"teko.agent.{self.name}")
        self.last_active = datetime.datetime.now()
        self.memory = {}
        self.status = "initialized"
        
    def update_status(self, status: str) -> None:
        """
        Update the agent's status.
        
        Args:
            status: The new status
        """
        self.logger.info(f"Agent {self.name} status changed: {self.status} -> {status}")
        self.status = status
        self.last_active = datetime.datetime.now()
        
    def log_error(self, error: Exception, context: Dict[str, Any] = None) -> None:
        """
        Log an error that occurred during agent execution.
        
        Args:
            error: The exception that occurred
            context: Additional context about what the agent was doing
        """
        self.logger.error(f"Error in agent {self.name}: {str(error)}", exc_info=True)
        error_data = {
            "agent": self.name,
            "agent_type": self.agent_type,
            "timestamp": datetime.datetime.now().isoformat(),
            "error": str(error),
            "error_type": type(error).__name__,
            "context": context or {}
        }
        
        # In a real implementation, this would store the error in a database
        self.logger.debug(f"Error data: {json.dumps(error_data)}")
        
    def store_in_memory(self, key: str, value: Any) -> None:
        """
        Store data in the agent's memory.
        
        Args:
            key: The key to store the data under
            value: The data to store
        """
        self.memory[key] = value
        
    def retrieve_from_memory(self, key: str) -> Optional[Any]:
        """
        Retrieve data from the agent's memory.
        
        Args:
            key: The key to retrieve
            
        Returns:
            The stored data or None if the key doesn't exist
        """
        return self.memory.get(key)
    
    @abc.abstractmethod
    def process_task(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """
        Process a task assigned to the agent.
        
        Args:
            task: The task data as a dictionary
            
        Returns:
            Dictionary containing the results of the task
        """
        pass
    
    @abc.abstractmethod
    def can_handle_task(self, task: Dict[str, Any]) -> bool:
        """
        Determine if this agent can handle the given task.
        
        Args:
            task: The task data as a dictionary
            
        Returns:
            True if the agent can handle the task, False otherwise
        """
        pass
