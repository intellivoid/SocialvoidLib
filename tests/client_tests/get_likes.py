import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)

response = request(
    client_info["endpoint"], "timeline.get_likes",
     session_identification={
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
     },
     post="e3c158bfc9495b94f7f768662b1cea58-a2d2b36c-1ed6-11ec-8d50-7baad10f2b00",
     offset=3, limit=25
)

print(json.dumps(response.data.result))
