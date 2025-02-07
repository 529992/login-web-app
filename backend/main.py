from fastapi import FastAPI, HTTPException, File, UploadFile, Form
from pydantic import BaseModel
import uvicorn
import firebase_admin
from firebase_admin import credentials, firestore
import os
import hashlib

app = FastAPI()

# Firebase initialization
cred = credentials.Certificate(r'C:\xampp\htdocs\login-web-app\config\j2b-login-details.json')
firebase_admin.initialize_app(cred)
db = firestore.client()

# Pydantic models for request validation
class RegisterModel(BaseModel):
    name: str
    email: str
    address: str
    mobile_number: str
    password: str

class LoginModel(BaseModel):
    email: str
    password: str

class UpdateAdminModel(BaseModel):
    name: str
    address: str
    mobile_number: str

@app.post("/register")
async def register_user(
    name: str = Form(...),
    email: str = Form(...),
    address: str = Form(...),
    mobile_number: str = Form(...),
    password: str = Form(...),
    profile_picture: UploadFile = File(None)
):
    try:
        # Check if admin already exists
        admins_ref = db.collection('admins')
        existing_admin = list(admins_ref.where('email', '==', email).limit(1).stream())
        
        if existing_admin:
            raise HTTPException(status_code=400, detail="Email already registered")
        
        # Hash password
        hashed_password = hashlib.sha256(password.encode()).hexdigest()
        
        # Prepare admin data
        admin_data = {
            'name': name,
            'email': email,
            'address': address,
            'mobile_number': mobile_number,
            'password': hashed_password
        }
        
        # Handle profile picture upload
        if profile_picture:
            upload_dir = r'C:\xampp\htdocs\login-web-app\uploads\profile_pictures'
            os.makedirs(upload_dir, exist_ok=True)
            file_path = os.path.join(upload_dir, f"{email}_{profile_picture.filename}")
            
            with open(file_path, "wb") as buffer:
                buffer.write(await profile_picture.read())
            
            admin_data['profile_picture_path'] = file_path
        
        # Add to Firestore
        _, doc_ref = admins_ref.add(admin_data)
        
        return {"status": "User registered successfully", "admin_id": doc_ref.id}
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/login")
async def login_user(login_data: LoginModel):
    try:
        # Hash the provided password
        hashed_password = hashlib.sha256(login_data.password.encode()).hexdigest()
        
        # Find admin by email and password
        admins_ref = db.collection('admins')
        query = admins_ref.where('email', '==', login_data.email).where('password', '==', hashed_password).limit(1)
        docs = list(query.stream())
        
        if not docs:
            raise HTTPException(status_code=401, detail="Invalid credentials")
        
        # Return admin ID
        admin_doc = docs[0]
        return {"status": "Login successful", "admin_id": admin_doc.id}
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/admin/update")
async def update_admin(
    name: str = Form(...),
    address: str = Form(...),
    mobile_number: str = Form(...),
    profile_picture: UploadFile = File(None)
):
    try:
        # In a real-world scenario, you'd get the admin_id from authentication
        # For this example, we'll use a placeholder
        admin_id = None  # Replace with actual authentication method
        
        if not admin_id:
            raise HTTPException(status_code=401, detail="Unauthorized")
        
        # Prepare update data
        update_data = {
            'name': name,
            'address': address,
            'mobile_number': mobile_number
        }
        
        # Handle profile picture upload
        if profile_picture:
            upload_dir = r'C:\xampp\htdocs\login-web-app\uploads\profile_pictures'
            os.makedirs(upload_dir, exist_ok=True)
            file_path = os.path.join(upload_dir, f"{admin_id}_{profile_picture.filename}")
            
            with open(file_path, "wb") as buffer:
                buffer.write(await profile_picture.read())
            
            update_data['profile_picture_path'] = file_path
        
        # Update in Firestore
        db.collection('admins').document(admin_id).update(update_data)
        
        return {"status": "Admin updated successfully"}
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/admin/profile/{admin_id}")
async def get_admin_profile(admin_id: str):
    try:
        admin_doc = db.collection('admins').document(admin_id).get()
        
        if not admin_doc.exists:
            raise HTTPException(status_code=404, detail="Admin not found")
        
        # Remove sensitive information before returning
        profile = admin_doc.to_dict()
        profile.pop('password', None)
        
        return profile
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)


