import os
import json
from jsonrpcclient import request

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)

response = request(
    client_info["endpoint"], "session.create",

     public_hash=client_info["public_hash"],
     private_hash=client_info["private_hash"],
     platform=client_info["platform"],
     name=client_info["name"],
     version=client_info["version"]
)

print(response.data.result)

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'w') as f:
    json.dump(response.data.result, f)