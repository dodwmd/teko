"""
LangChain Integration Wrapper

This module provides a wrapper around LangChain functionality for Teko agents.
It handles model initialization, prompt management, and templating.
"""

import logging
from typing import Any, Dict, List, Optional

from langchain.chains import LLMChain
from langchain.embeddings.openai import OpenAIEmbeddings
from langchain.memory import ConversationBufferMemory

# LangChain imports
from langchain.prompts import PromptTemplate
from langchain.schema import Document
from langchain_community.vectorstores import Chroma
from langchain_openai import ChatOpenAI

# Configure logging
logging.basicConfig(
    level=logging.INFO, format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)


class TekoChatModel:
    """
    Wrapper around LangChain's ChatOpenAI model with additional functionality.
    """

    def __init__(self, model_name: str = "gpt-4o", temperature: float = 0.1):
        """
        Initialize the chat model.

        Args:
            model_name: The name of the OpenAI model to use
            temperature: Temperature setting for output generation
        """
        self.logger = logging.getLogger("teko.langchain.chat")

        # Initialize the LLM
        self.llm = ChatOpenAI(model=model_name, temperature=temperature)

        self.logger.info(f"Initialized ChatOpenAI with model: {model_name}")

    def create_chain(
        self, prompt_template: str, memory: Optional[ConversationBufferMemory] = None
    ) -> LLMChain:
        """
        Create a LangChain LLMChain with the specified prompt template.

        Args:
            prompt_template: The template string for the prompt
            memory: Optional conversation memory to use

        Returns:
            An initialized LLMChain
        """
        prompt = PromptTemplate.from_template(prompt_template)

        if memory:
            chain = LLMChain(llm=self.llm, prompt=prompt, memory=memory)
        else:
            chain = LLMChain(llm=self.llm, prompt=prompt)

        return chain

    def generate_response(self, chain: LLMChain, **kwargs) -> str:
        """
        Generate a response using the provided chain and input variables.

        Args:
            chain: The LLMChain to use for generation
            **kwargs: Input variables for the chain

        Returns:
            The generated response as a string
        """
        try:
            response: str = chain.run(**kwargs)
            return response
        except Exception as e:
            self.logger.error(f"Error generating response: {str(e)}", exc_info=True)
            return f"Error: {str(e)}"


class TekoVectorStore:
    """
    Wrapper around LangChain's vector store for knowledge retrieval.
    """

    def __init__(self, collection_name: str):
        """
        Initialize the vector store.

        Args:
            collection_name: Name of the collection to store vectors in
        """
        self.logger = logging.getLogger("teko.langchain.vectorstore")
        self.collection_name = collection_name

        # Initialize the embeddings
        self.embeddings = OpenAIEmbeddings()

        # Initialize ChromaDB
        self.vectorstore = Chroma(
            collection_name=collection_name,
            embedding_function=self.embeddings,
            persist_directory="./data/vectorstore",
        )

        self.logger.info(f"Initialized vectorstore with collection: {collection_name}")

    def add_texts(
        self, texts: List[str], metadatas: Optional[List[Dict[str, Any]]] = None
    ) -> List[str]:
        """
        Add texts to the vector store.

        Args:
            texts: List of text strings to add
            metadatas: Optional list of metadata dictionaries for each text

        Returns:
            List of IDs for the added texts
        """
        ids = self.vectorstore.add_texts(texts=texts, metadatas=metadatas)
        return ids

    def add_documents(self, documents: List[Document]) -> List[str]:
        """
        Add documents to the vector store.

        Args:
            documents: List of Document objects to add

        Returns:
            List of IDs for the added documents
        """
        ids = self.vectorstore.add_documents(documents=documents)
        return ids

    def similarity_search(self, query: str, k: int = 5) -> List[Document]:
        """
        Perform a similarity search using the vector store.

        Args:
            query: The query string
            k: Number of results to return

        Returns:
            List of Documents matching the query
        """
        docs = self.vectorstore.similarity_search(query=query, k=k)
        return docs

    def similarity_search_with_score(self, query: str, k: int = 5) -> List[tuple[Document, float]]:
        """
        Perform a similarity search with relevance scores.

        Args:
            query: The query string
            k: Number of results to return

        Returns:
            List of (Document, score) tuples
        """
        docs_and_scores = self.vectorstore.similarity_search_with_score(query=query, k=k)
        return docs_and_scores
