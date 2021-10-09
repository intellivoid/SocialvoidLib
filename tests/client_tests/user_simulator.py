import os
import json
import random
from random import randint
from time import sleep
from faker import Faker
from jsonrpcclient import request
from session_challenge import answer_challenge

with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'client_info.json'), 'r') as f:
    client_info = json.load(f)
fake = Faker()

print("User Simulator for SocialvoidLib 1.0")
print("Generating a fake user ...")

simulator = {
    "profile": {
        "username": fake.simple_profile()['username'],
        "first_name": fake.first_name(),
        "last_name": fake.last_name(),
        "password": fake.password(31)
    }
}

print("Creating a session")

response = request(
    client_info["endpoint"], "session.create",
     public_hash=client_info["public_hash"],
     private_hash=client_info["private_hash"],
     platform=client_info["platform"],
     name=client_info["name"],
     version=client_info["version"]
)
print(" <-- " + json.dumps(response.data.result))
simulator["session"] = response.data.result
print("Saving profile")
with open(os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'simulations', simulator['profile']['username'] + '.json'), 'w') as f:
    json.dump(simulator, f)

print("Registering as {0}, using the password {1}".format(simulator["profile"]["username"], simulator["profile"]["password"]))
tos_id = request(client_info["endpoint"], "help.get_terms_of_service").data.result["id"]
response = request(
    client_info["endpoint"], "session.register",

     session_identification={
        "session_id": simulator["session"]["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
     },

     terms_of_service_id=tos_id,
     terms_of_service_agree=True,

     username=simulator["profile"]["username"],
     password=simulator["profile"]["password"],
     first_name=simulator["profile"]["first_name"],
     last_name=simulator["profile"]["last_name"]
)
print(" <-- " + json.dumps(response.data.result))

print("Logging in")
response = request(
    client_info["endpoint"], "session.authenticate_user",
     session_identification={
        "session_id": simulator["session"]["id"],
        "client_public_hash": client_info["public_hash"],
        "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
     },
     username=simulator["profile"]["username"],
     password=simulator["profile"]["password"]
)
print(" <-- " + json.dumps(response.data.result))

actions = [
    "compose_post",
    "follow_peer",
    "follow_vip_peer",
    "retrieve_timeline"
]

def random_peer():
    dir = os.path.join(os.path.dirname(os.path.realpath(__file__)), 'data', 'simulations')
    return random.choice(os.listdir(dir)).replace('.json', '')

while True:
    sleep(randint(1,2))
    selected_action = random.choice(actions)
    print(' > [{0}]'.format(selected_action))

    if selected_action == "compose_post":
        post_text = fake.paragraph()
        print("Composing a new post with '{0}'".format(post_text))
        response = request(
            client_info["endpoint"], "timeline.compose_post",
            session_identification={
                "session_id": simulator["session"]["id"],
                "client_public_hash": client_info["public_hash"],
                "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
            },
            text=post_text
        )
        print(" <-- " + json.dumps(response.data.result))
    if selected_action == "follow_peer":
        selected_peer = random_peer()
        print("Following peer @{0}".format(selected_peer))
        response = request(
            client_info["endpoint"], "network.follow_peer",
            session_identification={
                "session_id": simulator["session"]["id"],
                "client_public_hash": client_info["public_hash"],
                "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
            },
            peer="@{0}".format(selected_peer)
        )
        print(" <-- " + json.dumps(response.data.result))
    if selected_action == "follow_vip_peer":
        selected_peer = random_peer()
        print("Following vip peer @netkas")
        response = request(
            client_info["endpoint"], "network.follow_peer",
            session_identification={
                "session_id": simulator["session"]["id"],
                "client_public_hash": client_info["public_hash"],
                "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
            },
            peer="@netkas"
        )
        print(" <-- " + json.dumps(response.data.result))
    if selected_action == "retrieve_timeline":
        timeline_response = request(
            client_info["endpoint"], "timeline.retrieve_timeline",
            session_identification={
                "session_id": simulator["session"]["id"],
                "client_public_hash": client_info["public_hash"],
                "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
            },
            page=1
        )
        timeline_actions = ["nothing","like", "unlike", "repost", "reply", "quote"]
        for post in timeline_response.data.result:
            timeline_selected_action = random.choice(timeline_actions)
            print(' Timeline > [{0}]'.format(timeline_selected_action))
            print(post["id"])
            if timeline_selected_action == "like":
                response = request(
                    client_info["endpoint"], "timeline.like_post",
                    session_identification={
                        "session_id": simulator["session"]["id"],
                        "client_public_hash": client_info["public_hash"],
                        "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
                    },
                    post_id=post["id"]
                )
                print(" <-- " + json.dumps(response.data.result))
            if timeline_selected_action == "unlike":
                response = request(
                    client_info["endpoint"], "timeline.unlike_post",
                    session_identification={
                        "session_id": simulator["session"]["id"],
                        "client_public_hash": client_info["public_hash"],
                        "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
                    },
                    post_id=post["id"]
                )
                print(" <-- " + json.dumps(response.data.result))
            if timeline_selected_action == "repost":
                try:
                    response = request(
                        client_info["endpoint"], "timeline.repost_post",
                        session_identification={
                            "session_id": simulator["session"]["id"],
                            "client_public_hash": client_info["public_hash"],
                            "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
                        },
                        post_id=post["id"]
                    )
                    print(" <-- " + json.dumps(response.data.result))
                except:
                    print(" <-- Already reposted")
            if timeline_selected_action == "reply":
                response = request(
                    client_info["endpoint"], "timeline.reply_to_post",
                    session_identification={
                        "session_id": simulator["session"]["id"],
                        "client_public_hash": client_info["public_hash"],
                        "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
                    },
                    post_id=post["id"],
                    text=fake.paragraph()
                )
                print(" <-- " + json.dumps(response.data.result))
            if timeline_selected_action == "quote":
                response = request(
                    client_info["endpoint"], "timeline.quote_post",
                    session_identification={
                        "session_id": simulator["session"]["id"],
                        "client_public_hash": client_info["public_hash"],
                        "challenge_answer": answer_challenge(client_info["private_hash"], simulator["session"]["challenge"])
                    },
                    post_id=post["id"],
                    text=fake.paragraph()
                )
                print(" <-- " + json.dumps(response.data.result))