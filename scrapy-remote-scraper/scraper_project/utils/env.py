import os
from dotenv import load_dotenv

load_dotenv()

def get_bool(name: str, default: bool = False) -> bool:
    value = os.getenv(name)
    if value is None:
        return default
    return value.strip().lower() in {"1", "true", "yes", "on"}

def get_int(name: str, default: int) -> int:
    value = os.getenv(name)
    if value is None:
        return default
    try:
        return int(value)
    except ValueError:
        return default

def get_float(name: str, default: float) -> float:
    value = os.getenv(name)
    if value is None:
        return default
    try:
        return float(value)
    except ValueError:
        return default

def get_list(name: str, separator: str = ",", default=None):
    if default is None:
        default = []
    value = os.getenv(name, "")
    if not value.strip():
        return default
    return [v.strip() for v in value.split(separator) if v.strip()]
