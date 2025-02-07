from pydantic import BaseModel

class UserModel(BaseModel):
    name: str
    email: str
    password: str

class AdminModel(BaseModel):
    name: str
    email: str
    password: str
    created_by: str

class LoginModel(BaseModel):
    email: str
    password: str