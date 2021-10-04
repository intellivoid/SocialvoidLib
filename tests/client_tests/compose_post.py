import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'session.social_session'), 'r') as f:
    session_info = json.load(f)

for _ in range(999999999999999999):
    response = request(
        client_info["endpoint"], "timeline.compose_post",
         session_identification={
            "session_id": session_info["id"],
            "client_public_hash": client_info["public_hash"],
            "challenge_answer": answer_challenge(client_info["private_hash"], session_info["challenge"])
         },
         text="Is it working? @admin #YOLO https://xvideos.com/ <--- Visit my onlyfans plis! @Google @admin @loco @netkas @netkas @non_valid @toua"
    )

    print(json.dumps(response.data.result))