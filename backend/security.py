import secrets
import hashlib
import os
from datetime import datetime, timedelta

class SecurityManager:
    @staticmethod
    def generate_salt():
        """Generate a secure random salt"""
        return secrets.token_hex(16)

    @staticmethod
    def hash_password(password, salt=None):
        """
        Securely hash password using SHA-256
        
        Args:
            password (str): Plain text password
            salt (str, optional): Predefined salt or generate new
        
        Returns:
            tuple: (hashed_password, salt)
        """
        if salt is None:
            salt = SecurityManager.generate_salt()
        
        # Combine password and salt
        salted_password = password + salt
        
        # Hash using SHA-256
        hashed_password = hashlib.sha256(salted_password.encode()).hexdigest()
        
        return hashed_password, salt

    @staticmethod
    def verify_password(stored_password, stored_salt, provided_password):
        """
        Verify if provided password matches stored password
        
        Args:
            stored_password (str): Previously hashed password
            stored_salt (str): Salt used in original hashing
            provided_password (str): Password to verify
        
        Returns:
            bool: Password verification result
        """
        hashed_provided_password, _ = SecurityManager.hash_password(
            provided_password, 
            salt=stored_salt
        )
        
        return secrets.compare_digest(stored_password, hashed_provided_password)

    @staticmethod
    def generate_token(user_id, token_type='access'):
        """
        Generate secure authentication token
        
        Args:
            user_id (str): User identifier
            token_type (str): Type of token (access or refresh)
        
        Returns:
            dict: Token details
        """
        token = secrets.token_urlsafe(32)
        expires_at = datetime.utcnow() + timedelta(
            hours=24 if token_type == 'access' else 30
        )
        
        return {
            'token': token,
            'user_id': user_id,
            'type': token_type,
            'created_at': datetime.utcnow(),
            'expires_at': expires_at
        }

    @staticmethod
    def validate_token(token, token_type='access'):
        """
        Validate authentication token
        
        Args:
            token (dict): Token to validate
            token_type (str): Expected token type
        
        Returns:
            bool: Token validity
        """
        if not token:
            return False
        
        # Check token type
        if token.get('type') != token_type:
            return False
        
        # Check expiration
        current_time = datetime.utcnow()
        expires_at = token.get('expires_at')
        
        return current_time < expires_at