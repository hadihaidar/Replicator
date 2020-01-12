from azure.storage.queue import QueueService
import base64
import time
import os
import requests
import json
import urllib
import urllib2

queue_service = QueueService(account_name="aubreplicatoraub", account_key="DFHKS0vAf0q3gd7WX9QMIH62cLxN3mr8HF3wmjsI52fnKtccuRNY/6gYixVZ+OUvPGK30R4xlrbQg4qT8W5wTA==")
postlink = "http://octopi.local/api/files/local?apikey=3678BEC930AA4AB5AA72266846820FB3"
directory = "/home/pi/.octoprint/uploads/"

# Dequeue Message Function
def DequeueMessage():
    messages = queue_service.get_messages('printjobs')
    for message in messages:
        # decode base64 
        decode = base64.b64decode(message.content)
        x = str(decode)
        queue_service.delete_message('printjobs', message.id, message.pop_receipt)
        return x

# Checks if printer is Operational
def isOperational():
    try:
        r = requests.get("http://octopi.local/api/connection?apikey=3678BEC930AA4AB5AA72266846820FB3")
        x = r.json()
        y = json.dumps(x)
        parsedJson = json.loads(y)
        return (parsedJson["current"]["state"]=="Operational")
    except:
        print("IS OPERATIONAL ERROR... Cannot Connect..... SERVER OFFLINE")

# Checks if Raspberry pi is connected to printer
def notConnected():
    try:
        r = requests.get("http://octopi.local/api/connection?apikey=3678BEC930AA4AB5AA72266846820FB3")
        x = r.json()
        y = json.dumps(x)
        parsedJson = json.loads(y)
        return (parsedJson["current"]["state"]=="Closed")
    except:
        print("NOT CONNECTED ERROR.... Cannot Connect..... SERVER OFFLINE")

# if(notConnected()):
#     print("Not Connected.... Connecting.....")
#     requests.post(url = "http://octopi.local/api/connection?apikey=3678BEC930AA4AB5AA72266846820FB3" , json = {'command' : 'connect'})
# print("Connected")

while(True):
    print("Inside the While True")
    if(notConnected()):
        print("Not Connected.... Connecting.....")
        requests.post(url = "http://octopi.local/api/connection?apikey=3678BEC930AA4AB5AA72266846820FB3" , json = {'command' : 'connect'})
    print("Connected")
    if(isOperational()):                        # Dequeue the url
        print("isOperational")
        modelurl = DequeueMessage()
        if(modelurl is not None):                                     # Example: https://aubreplicator.blob.core.windows.net/3d-models/modelname.gcode
            #print(modelurl)
            print("Model URL = " + modelurl)
            ar = modelurl.split("/")
            filename = ar[-1]
            #filename = modelurl.rsplit('/', 1)[1]                           # Example: modelname.gcode
            print("File Name = " + filename)
            # Change directory
            os.chdir(directory)
            print("Directory changed to " + str(directory))
            pathFileName = str(directory + filename)
            print("File path = " + pathFileName)
            fileexists = os.path.isfile(directory + filename)               # Example : /home/pi/.octoprint/uploads/modelname.gcode
            if not fileexists:                      # if file doesn't exist
                # Download the file
                print("Downloading File.....")
                #wget.download(modelurl)
                #urllib.request.urlretrieve(modelurl, pathFileName)
                web_file = urllib2.urlopen(modelurl)
                out_file = open(pathFileName, 'w')
                out_file.write(web_file.read())
                out_file.close()
            # Print the File
            print("Printing File.....")
            requests.post(url = "http://octopi.local/api/files/local?apikey=3678BEC930AA4AB5AA72266846820FB3", files={'file': open(pathFileName, 'rb')}, data={"select": "true", "print": "true"})
        else:
            print("printjobs == []")
            print("Sleep 10 sec")
            time.sleep(10)                                                   # wait 10 sec
    else:
        print("Sleep 30 sec")
        time.sleep(30)                                                      # wait for half a minute
