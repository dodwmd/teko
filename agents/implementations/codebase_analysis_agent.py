"""
Codebase Analysis Agent

This agent analyzes repositories to identify languages, frameworks, 
and extract codebase metadata. It helps other agents understand 
the codebase structure and characteristics.
"""

import os
import json
import logging
from typing import Dict, List, Any, Optional, Set
import re

from ..core.base_agent import BaseAgent
from ..core.langchain_wrapper import TekoChatModel, TekoVectorStore

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)

class CodebaseAnalysisAgent(BaseAgent):
    """
    Agent for analyzing codebases, detecting languages, and extracting metadata.
    """
    
    def __init__(self, name: str, agent_type: str = "codebase_analysis", config: Dict[str, Any] = None):
        """
        Initialize the codebase analysis agent.
        
        Args:
            name: The name of the agent
            agent_type: The type of agent (should be "codebase_analysis")
            config: Optional configuration dictionary
        """
        super().__init__(name, agent_type, config)
        
        # Initialize language detection thresholds and patterns
        self.language_extensions = {
            "php": [".php"],
            "python": [".py", ".pyx", ".pyi"],
            "javascript": [".js", ".jsx", ".ts", ".tsx"],
            "java": [".java"],
            "ruby": [".rb"],
            "go": [".go"],
            "rust": [".rs"],
            "c": [".c", ".h"],
            "cpp": [".cpp", ".hpp", ".cc", ".hh"],
            "csharp": [".cs"],
            "swift": [".swift"],
            "kotlin": [".kt"],
            "html": [".html", ".htm"],
            "css": [".css", ".scss", ".sass", ".less"],
        }
        
        # Framework detection patterns
        self.framework_patterns = {
            "php": {
                "laravel": ["laravel", "illuminate", "artisan"],
                "symfony": ["symfony", "doctrine", "twig"],
                "wordpress": ["wp-", "wordpress", "wp_"],
                "drupal": ["drupal", r"\bDrupal::"],
            },
            "python": {
                "django": ["django", "urls.py", "wsgi.py", "asgi.py"],
                "flask": ["flask", "app.route", "@app.route"],
                "fastapi": ["fastapi", "app.get", "@app.get"],
                "pytorch": ["torch", "nn.Module"],
                "tensorflow": ["tensorflow", "tf."],
            },
            "javascript": {
                "react": ["react", "useState", "useEffect", "Component"],
                "angular": ["angular", "@Component", "ngModule"],
                "vue": ["vue", "createApp", "defineComponent"],
                "next": ["next", "getStaticProps", "getServerSideProps"],
                "node": ["express", "require(", "npm", "package.json"],
            }
        }
        
        # Initialize AI components if enabled in config
        self.ai_enabled = self.config.get("ai_enabled", True)
        if self.ai_enabled:
            self.chat_model = TekoChatModel(
                model_name=self.config.get("model_name", "gpt-4o"),
                temperature=self.config.get("temperature", 0.1)
            )
            self.vector_store = TekoVectorStore(
                collection_name=f"codebase_analysis_{self.name}"
            )
    
    def analyze_file_extensions(self, file_paths: List[str]) -> Dict[str, int]:
        """
        Analyze file extensions to determine language distribution.
        
        Args:
            file_paths: List of file paths in the repository
            
        Returns:
            Dictionary mapping languages to file counts
        """
        language_counts = {}
        
        for path in file_paths:
            ext = os.path.splitext(path)[1].lower()
            
            # Skip directories and files without extensions
            if not ext:
                continue
            
            # Find which language this extension belongs to
            for language, extensions in self.language_extensions.items():
                if ext in extensions:
                    language_counts[language] = language_counts.get(language, 0) + 1
                    break
        
        return language_counts
    
    def detect_frameworks(self, file_contents: Dict[str, str], primary_language: str) -> Dict[str, float]:
        """
        Detect frameworks used in the codebase.
        
        Args:
            file_contents: Dictionary mapping file paths to their contents
            primary_language: The primary language detected in the codebase
            
        Returns:
            Dictionary mapping framework names to confidence scores (0-1)
        """
        framework_counts = {}
        
        # Only check for frameworks in the primary language
        if primary_language not in self.framework_patterns:
            return {}
        
        frameworks = self.framework_patterns[primary_language]
        
        # Count framework pattern occurrences
        for path, content in file_contents.items():
            for framework, patterns in frameworks.items():
                for pattern in patterns:
                    # Count occurrences of the pattern
                    if re.search(pattern, content, re.IGNORECASE):
                        framework_counts[framework] = framework_counts.get(framework, 0) + 1
        
        # Convert counts to confidence scores
        total_files = len(file_contents)
        confidence_scores = {
            framework: min(1.0, count / (total_files * 0.1))  # Normalize scores
            for framework, count in framework_counts.items()
        }
        
        return confidence_scores
    
    def extract_dependencies(self, file_contents: Dict[str, str], primary_language: str) -> Dict[str, List[str]]:
        """
        Extract dependencies from package files based on language.
        
        Args:
            file_contents: Dictionary mapping file paths to their contents
            primary_language: The primary language detected
            
        Returns:
            Dictionary of dependencies by type
        """
        dependencies = {}
        
        # Language-specific package files
        package_files = {
            "php": ["composer.json"],
            "python": ["requirements.txt", "setup.py", "pyproject.toml", "Pipfile"],
            "javascript": ["package.json"]
        }
        
        if primary_language not in package_files:
            return dependencies
        
        # Extract from composer.json for PHP
        if primary_language == "php" and "composer.json" in file_contents:
            try:
                composer_data = json.loads(file_contents["composer.json"])
                require = composer_data.get("require", {})
                require_dev = composer_data.get("require-dev", {})
                
                dependencies["production"] = [f"{pkg}:{ver}" for pkg, ver in require.items()]
                dependencies["development"] = [f"{pkg}:{ver}" for pkg, ver in require_dev.items()]
            except json.JSONDecodeError:
                self.logger.error("Failed to parse composer.json")
        
        # Extract from package.json for JavaScript
        elif primary_language == "javascript" and "package.json" in file_contents:
            try:
                package_data = json.loads(file_contents["package.json"])
                dependencies_dict = package_data.get("dependencies", {})
                dev_dependencies = package_data.get("devDependencies", {})
                
                dependencies["production"] = [f"{pkg}:{ver}" for pkg, ver in dependencies_dict.items()]
                dependencies["development"] = [f"{pkg}:{ver}" for pkg, ver in dev_dependencies.items()]
            except json.JSONDecodeError:
                self.logger.error("Failed to parse package.json")
        
        # Extract from requirements.txt for Python
        elif primary_language == "python" and "requirements.txt" in file_contents:
            requirements = file_contents["requirements.txt"].splitlines()
            dependencies["production"] = [
                line.strip() for line in requirements 
                if line.strip() and not line.startswith("#")
            ]
        
        return dependencies
    
    def get_ai_insights(self, file_contents: Dict[str, str], primary_language: str) -> Dict[str, Any]:
        """
        Use AI to gain deeper insights about the codebase structure and patterns.
        
        Args:
            file_contents: Dictionary mapping file paths to their contents
            primary_language: The primary language detected
            
        Returns:
            Dictionary of AI-generated insights
        """
        if not self.ai_enabled:
            return {"ai_enabled": False}
        
        # Select representative files for analysis
        representative_files = self._select_representative_files(file_contents, primary_language, max_files=5)
        if not representative_files:
            return {"error": "No representative files found for analysis"}
        
        # Prepare prompt for the AI
        prompt_template = """
        Analyze the following code snippets from a {language} codebase and provide insights:
        
        {file_snippets}
        
        Please provide the following information:
        1. Architecture patterns: Identify any architectural patterns (MVC, MVVM, microservices, etc.)
        2. Code quality: Assess the overall code quality, organization, and naming conventions
        3. Potential challenges: Identify potential maintenance challenges or technical debt
        4. Special patterns: Note any unique coding patterns or custom abstractions
        5. Best practices: Identify adherence or deviation from {language} best practices
        
        Format your response as JSON with these keys.
        """
        
        # Create file snippets
        file_snippets = []
        for path, content in representative_files.items():
            # Limit content length for prompt
            if len(content) > 2000:
                content = content[:2000] + "... [truncated]"
            
            file_snippets.append(f"File: {path}\n```\n{content}\n```\n")
        
        # Create chain and generate response
        chain = self.chat_model.create_chain(prompt_template)
        
        try:
            response = self.chat_model.generate_response(
                chain,
                language=primary_language,
                file_snippets="\n".join(file_snippets)
            )
            
            # Try to parse JSON response
            try:
                insights = json.loads(response)
                return insights
            except json.JSONDecodeError:
                # If not valid JSON, return as text
                return {"raw_insights": response}
                
        except Exception as e:
            self.logger.error(f"Error generating AI insights: {str(e)}")
            return {"error": str(e)}
    
    def _select_representative_files(
        self, file_contents: Dict[str, str], language: str, max_files: int = 5
    ) -> Dict[str, str]:
        """
        Select representative files from the codebase for analysis.
        
        Args:
            file_contents: Dictionary mapping file paths to their contents
            language: The primary language to focus on
            max_files: Maximum number of files to select
            
        Returns:
            Dictionary of selected files and their contents
        """
        selected_files = {}
        
        # Get list of files with the target language extensions
        language_extensions = self.language_extensions.get(language, [])
        language_files = [
            path for path in file_contents.keys()
            if any(path.endswith(ext) for ext in language_extensions)
        ]
        
        if not language_files:
            return {}
        
        # Priority files to include based on patterns
        priority_patterns = {
            "php": ["config", "app/Http/Controllers", "app/Models", "routes", "composer.json"],
            "python": ["settings.py", "urls.py", "models.py", "views.py", "app.py", "main.py"],
            "javascript": ["index.js", "app.js", "components", "src/App", "package.json"]
        }
        
        patterns = priority_patterns.get(language, [])
        
        # First select priority files
        for pattern in patterns:
            if len(selected_files) >= max_files:
                break
                
            for path in language_files:
                if pattern in path and path not in selected_files:
                    selected_files[path] = file_contents[path]
                    break
        
        # Fill remaining slots with other files
        remaining_slots = max_files - len(selected_files)
        if remaining_slots > 0:
            for path in language_files:
                if path not in selected_files:
                    selected_files[path] = file_contents[path]
                    remaining_slots -= 1
                    if remaining_slots <= 0:
                        break
        
        return selected_files
    
    def process_task(self, task: Dict[str, Any]) -> Dict[str, Any]:
        """
        Process a codebase analysis task.
        
        Args:
            task: The task data dictionary containing repository information
            
        Returns:
            Analysis results
        """
        self.update_status("analyzing")
        
        # Extract repository information from task
        repo_url = task.get("repository_url")
        repo_id = task.get("repository_id")
        file_paths = task.get("file_paths", [])
        file_contents = task.get("file_contents", {})
        
        if not file_paths or not file_contents:
            raise ValueError("Task missing required file_paths or file_contents")
        
        # Analyze file extensions to determine language distribution
        language_counts = self.analyze_file_extensions(file_paths)
        
        # Determine primary language
        primary_language = None
        if language_counts:
            primary_language = max(language_counts, key=language_counts.get)
        
        # Detect frameworks
        frameworks = {}
        if primary_language:
            frameworks = self.detect_frameworks(file_contents, primary_language)
        
        # Extract dependencies
        dependencies = {}
        if primary_language:
            dependencies = self.extract_dependencies(file_contents, primary_language)
        
        # Get AI insights if enabled
        ai_insights = {}
        if self.ai_enabled and primary_language:
            ai_insights = self.get_ai_insights(file_contents, primary_language)
        
        # Compile results
        results = {
            "repository_url": repo_url,
            "repository_id": repo_id,
            "file_count": len(file_paths),
            "languages": language_counts,
            "primary_language": primary_language,
            "frameworks": frameworks,
            "dependencies": dependencies,
            "ai_insights": ai_insights,
            "timestamp": self.last_active.isoformat()
        }
        
        self.update_status("completed")
        return results
    
    def can_handle_task(self, task: Dict[str, Any]) -> bool:
        """
        Determine if this agent can handle the given task.
        
        Args:
            task: The task data dictionary
            
        Returns:
            True if the agent can handle the task, False otherwise
        """
        # This agent handles codebase analysis tasks
        task_type = task.get("type", "").lower()
        return task_type == "codebase_analysis" and "repository_id" in task
