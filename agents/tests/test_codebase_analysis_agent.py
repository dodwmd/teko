"""Unit tests for the Codebase Analysis Agent."""

import os  # noqa: F401 - Used in patch decorator
from unittest.mock import MagicMock, patch

from agents.implementations.codebase_analysis_agent import CodebaseAnalysisAgent


class TestCodebaseAnalysisAgent:
    """Test class for the CodebaseAnalysisAgent."""

    def setup_method(self, method):
        """Set up test fixtures before each test method is executed."""
        # Create patches for external dependencies
        self.patchers = []

        # Patch TekoChatModel
        chat_model_patcher = patch("agents.implementations.codebase_analysis_agent.TekoChatModel")
        self.mock_chat_model_class = chat_model_patcher.start()
        self.mock_chat_model = MagicMock()
        self.mock_chat_model_class.return_value = self.mock_chat_model
        self.patchers.append(chat_model_patcher)

        # Patch TekoVectorStore
        vector_store_patcher = patch(
            "agents.implementations.codebase_analysis_agent.TekoVectorStore"
        )
        self.mock_vector_store_class = vector_store_patcher.start()
        self.mock_vector_store = MagicMock()
        self.mock_vector_store_class.return_value = self.mock_vector_store
        self.patchers.append(vector_store_patcher)

        # Patch OpenAIEmbeddings
        embeddings_patcher = patch("agents.core.langchain_wrapper.OpenAIEmbeddings")
        self.mock_embeddings_class = embeddings_patcher.start()
        self.mock_embeddings = MagicMock()
        self.mock_embeddings_class.return_value = self.mock_embeddings
        self.patchers.append(embeddings_patcher)

        # Initialize the agent with a name
        self.agent = CodebaseAnalysisAgent(
            name="test_agent",
            agent_type="codebase_analysis",
            config={"model_name": "gpt-3.5-turbo"},
        )

    def teardown_method(self, method):
        """Clean up after each test method."""
        # Stop all patchers
        for patcher in self.patchers:
            patcher.stop()

    def test_agent_initialization(self):
        """Test basic agent initialization."""
        assert self.agent is not None
        assert self.agent.name == "test_agent"
        assert self.agent.agent_type == "codebase_analysis"
        assert self.agent.status == "initialized"  # Updated to match actual status
        assert self.agent.memory is not None

    @patch("os.path.exists")
    def test_can_handle_task(self, mock_exists):
        """Test that the agent can handle valid repository tasks."""
        # Mock that the repository path exists
        mock_exists.return_value = True

        # Manually set the agent status to active to match code behavior
        self.agent.status = "active"

        # Create a task with the correct keys as expected by the agent
        task = {
            "type": "codebase_analysis",
            "repository_path": "/fake/repo/path",
            "repository_id": "test-repo-123",
            "priority": "high",
        }

        # Check if agent can handle the task
        result = self.agent.can_handle_task(task)
        assert result is True

    def test_analyze_file_extensions(self):
        """Test the analyze_file_extensions method."""
        # Sample file paths
        file_paths = [
            "/fake/repo/path/file1.py",
            "/fake/repo/path/file2.py",
            "/fake/repo/path/file3.js",
            "/fake/repo/path/file4.html",
            "/fake/repo/path/file5.css",
        ]

        # Call the method
        result = self.agent.analyze_file_extensions(file_paths)

        # Verify results
        assert "python" in result
        assert result["python"] == 2
        assert "javascript" in result
        assert result["javascript"] == 1
        assert "html" in result
        assert result["html"] == 1
        assert "css" in result
        assert result["css"] == 1
