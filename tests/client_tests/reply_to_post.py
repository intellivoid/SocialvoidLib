import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)

response = request(
    client_info["endpoint"], "timeline.reply",
     session_identification={
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
     },
     post="35714fd511b6064908da4ed77c12f587-789a1a44-251c-11ec-aec0-113646ee4b39",
     text="This is a reply to a post #ReplyMoment #EatMyAss"
)

print(json.dumps(response.data.result))
