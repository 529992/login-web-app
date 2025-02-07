from fastapi import FastAPI, File, UploadFile, HTTPException
from pydantic import BaseModel
from typing import Optional
import re

class AdminRegistrationModel(BaseModel):
    name: str
    email: str
    address: str
    mobile_number: str
    password: str

def validate_email(email):
    """
    Basic email validation
    """
    email_regex = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
    return re.match(email_regex, email) is not None

def validate_mobile_number(mobile):
    """
    Basic mobile number validation (adjust regex for your region)
    """
    mobile_regex = r'^\+?1?\d{10,14}$'
    return re.match(mobile_regex, mobile) is not None

def validate_admin_registration(admin_data: AdminRegistrationModel):
    """
    Comprehensive validation for admin registration
    """
    errors = []

    # Name validation
    if not admin_data.name or len(admin_data.name) < 2:
        errors.append("Name must be at least 2 characters long")

    # Email validation
    if not validate_email(admin_data.email):
        errors.append("Invalid email format")

    # Mobile number validation
    if not validate_mobile_number(admin_data.mobile_number):
        errors.append("Invalid mobile number")

    # Password complexity (example)
    if len(admin_data.password) < 8:
        errors.append("Password must be at least 8 characters long")

    if errors:
        raise HTTPException(status_code=400, detail=errors)

    return True