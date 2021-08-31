import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)

print(" === PRIVACY POLICY === ")
print(request(client_info["endpoint"], "network.get_privacy_policy").data.result)
print()

print(" === TERMS OF SERVICE === ")
print(request(client_info["endpoint"], "network.get_terms_of_service").data.result)
print()

print(" === COMMUNITY GUIDELINES === ")
print(request(client_info["endpoint"], "network.get_community_guidelines").data.result)
print()