import firebase_admin
from firebase_admin import credentials, firestore

class DatabaseManager:
    def __init__(self, credentials_path):
        # Check if Firebase app is already initialized
        if not firebase_admin._apps:
            cred = credentials.Certificate(credentials_path)
            firebase_admin.initialize_app(cred)
        
        self.db = firestore.client()
        self.admins_collection = self.db.collection('admins')

    
    def create_admin_profile(self, admin_id, admin_details):
        """
        Create or update admin profile in Firestore
        """
        try:
            # Sanitize and validate admin details
            profile_data = {
                'name': admin_details.get('name', ''),
                'email': admin_details.get('email', ''),
                'address': admin_details.get('address', ''),
                'mobile_number': admin_details.get('mobile_number', ''),
                'profile_picture_path': admin_details.get('profile_picture_path', ''),
                'e_signature_path': admin_details.get('e_signature_path', '')
            }

            # Add or update admin profile
            self.admins_collection.document(admin_id).set(profile_data, merge=True)
            return True
        except Exception as e:
            print(f"Error creating admin profile: {e}")
            return False