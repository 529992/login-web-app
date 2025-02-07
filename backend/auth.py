from fastapi import HTTPException
from firebase_admin import auth
import firebase_admin
from firebase_admin import credentials, firestore
from .security import SecurityManager
from .database import DatabaseManager

class AuthManager:
    def __init__(self, credentials_path):
        # Initialize Firebase Admin SDK
        if not firebase_admin._apps:
            cred = credentials.Certificate(credentials_path)
            firebase_admin.initialize_app(cred)
        
        self.db = firestore.client()
        self.security_manager = SecurityManager()
        self.database_manager = DatabaseManager(credentials_path)

    def create_admin_account(self, email, password, admin_details):
        try:
            # Hash password
            hashed_password, salt = self.security_manager.hash_password(password)
            
            # Add password hash and salt to admin details
            admin_details['password_hash'] = hashed_password
            admin_details['password_salt'] = salt
            
            # Create user in Firebase Authentication
            user = auth.create_user(
                email=email,
                password=password  # Firebase will handle its own password hashing
            )
            
            # Store additional admin details in Firestore
            self.database_manager.create_admin_profile(user.uid, admin_details)
            
            return user.uid
        except Exception as e:
            raise HTTPException(status_code=400, detail=str(e))

    def authenticate_admin(self, email, password):
        try:
            # Find user by email in Firestore
            admins_ref = self.db.collection('admins')
            query = admins_ref.where('email', '==', email).limit(1)
            docs = query.stream()
            
            for doc in docs:
                admin_data = doc.to_dict()
                
                # Verify password
                if self.security_manager.verify_password(
                    admin_data['password_hash'],
                    admin_data['password_salt'],
                    password
                ):
                    # Generate access and refresh tokens
                    access_token = self.security_manager.generate_token(doc.id)
                    refresh_token = self.security_manager.generate_token(doc.id, 'refresh')
                    
                    # Store tokens in user's document
                    admins_ref.document(doc.id).update({
                        'access_token': access_token,
                        'refresh_token': refresh_token
                    })
                    
                    return {
                        'admin_id': doc.id,
                        'access_token': access_token['token'],
                        'refresh_token': refresh_token['token']
                    }
            
            raise HTTPException(status_code=401, detail="Invalid credentials")
        
        except Exception as e:
            raise HTTPException(status_code=401, detail="Authentication failed")