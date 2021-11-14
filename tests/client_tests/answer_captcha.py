import os
import json
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)

print(request(client_info["endpoint"], "captcha.answer", answer="khvu9", captcha="9ded46b4d85e109b685b310d77e64ada-9171b164-4577-11ec-97db-e79b9ec3c14b").data.result)
