import os
import json
import requests
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)
file_upload = os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json')

# Get the CDN Server endpoint
cdn_endpoint = request(client_info["endpoint"], "help.get_server_information").data.result['cdn_server']

print("Uploading document ...")
r = requests.post(
    cdn_endpoint,
    files={"document": open(file_upload, "rb")},
    data={
        "action": "upload",
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
    }
)

print(r.text)
print()

document_id = json.loads(r.text)["results"]["id"]
print(document_id)
print("Attaching document to post")

response = request(
    client_info["endpoint"], "timeline.compose",
    session_identification={
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
    },
    text="Is it working? @admin #YOLO https://xvideos.com/ <--- Visit my onlyfans plis! @Google @admin @loco @netkas @netkas @non_valid @toua",
    attachments=[document_id]
)
print(json.dumps(response.data.result))