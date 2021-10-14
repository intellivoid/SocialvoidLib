import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)

print("loading")
response = request(
    client_info["endpoint"], "timeline.get_replies",
     session_identification={
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
     },
     post="67e69af2653afd783a3e41d375ba2e73-cc58c36e-2ced-11ec-8611-35a86dd08d2a",
     cursor=1
)

print(json.dumps(response.data.result))
