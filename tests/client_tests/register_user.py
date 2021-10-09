import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)

tos_id = request(client_info["endpoint"], "help.get_terms_of_service").data.result["id"]

response = request(
    client_info["endpoint"], "session.register",

     session_identification={
        "session_id": session_info["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
     },

     terms_of_service_id=tos_id,
     terms_of_service_agree=True,

     username="netkas",
     password="SuperSimplePassword123",
     first_name="Zi",
     last_name="Xing"
)

print(response.data.result)
