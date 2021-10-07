import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)

# Get the CDN Server endpoint
cdn_endpoint = request(client_info["endpoint"], "help.get_server_information").data.result['cdn_server']

response = request(
    client_info["endpoint"], "cloud.get_document",
     session_identification={
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
     },
     document="35714fd511b6064908da4ed77c12f587-d0b930a2d8310098553f627f6f295cb464d43c8f52b4a58cbeb95e318187cafa-8e278294"
)

#print(response.data.result)

print("{0}?action=download&document={1}&session_id={2}&client_public_hash={3}&challenge_answer={4}".format(
    cdn_endpoint,
    response.data.result['id'],
    session_info["id"],
    client_info["public_hash"],
    answer_challenge(client_info["private_hash"], session_info["challenge"])
))
