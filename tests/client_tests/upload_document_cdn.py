import os
import json
import requests
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)
file_upload = os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'file_example_MP3_5MG.mp3')

# Get the CDN Server endpoint
cdn_endpoint = request(client_info["endpoint"], "help.get_server_information").data.result['cdn_server']

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
print("{0}?action=download&document={1}&session_id={2}&client_public_hash={3}&challenge_answer={4}".format(
    cdn_endpoint,
    document_id,
    session_info["id"],
    client_info["public_hash"],
    answer_challenge(client_info["private_hash"], session_info["challenge"])
))