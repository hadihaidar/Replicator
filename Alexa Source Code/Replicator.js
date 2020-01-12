const Alexa = require('alexa-sdk');
var https = require('https');
var request = require('request');
const APP_ID = 'amzn1.ask.skill.4edb7d7e-d9d6-4d2d-8636-e9d8de4921b4';
var models = [];
var allMOdels = [];

exports.handler = function(event, context, callback) {
    const alexa = Alexa.handler(event, context, callback);
    alexa.APP_ID = APP_ID;
    alexa.registerHandlers(newSessionHandler, ReadModelsHandler, continueHandler, ReadModelsHelperHandler);
    alexa.execute();
};

const states = {
    "STARTMODE": "_STARTMODE",
    "STARTHELPMODE": "_STARTHELPMODE",
    "CONTINUEMODE": "_CONTINUEMODE"
}



https.get('https://replicatorservices.azurewebsites.net/api/GetListOfModels?code=rMntaUlGc23ph9o2Mc67TScAUZQNMUV8Xac//lbjY74dqh47X2l8zQ==', (resp) => {
    //let models = '';

    // A chunk of data has been recieved.
    resp.on('data', (chunk) => {
        models += chunk;
        models = JSON.parse(models);
        for (var i = 0; i < models.length; i++) {
            allMOdels.push(models[i].name);
        }
        console.log(allMOdels);
    });

    // The whole response has been received. Print out the result.
    resp.on('end', () => {
        //console.log(JSON.parse(models).explanation);
    });

}).on("error", (err) => {
    console.log("Error: " + err.message);
});



function getListOfCommonWords(modelRequested, AllModels) {
    var listOfCommons = [];
    for (var i = 0; i < AllModels.length; i++) {
        if (hasWordInCommon(modelRequested, AllModels[i]) == true) {
            listOfCommons.push(AllModels[i]);
        }
    }
    return listOfCommons;
}

function hasWordInCommon(modelRequested, modelPresent) {
    var modelRequestedSplitted = modelRequested.split(" ");
    var modelPresentSplitted = modelPresent.split(" ");
    for (var i = 0; i < modelRequestedSplitted.length; i++) {
        for (var j = 0; j < modelPresentSplitted.length; j++) {
            if (modelRequestedSplitted[i].toLowerCase() == modelPresentSplitted[j].toLowerCase()) {
                return true;
            }
        }
    }
    return false;
}

const newSessionHandler = {
    'LaunchRequest'      : function() {
        this.attributes["currentListIndex"] = 0;
        const welcomeMessage = 'Welcome to The Replicator. Do you want me to give you a list of models you can print?.';
        const repromptMessage = 'Do you want me to give you a list of models you can print?';
        this.handler.state = states.STARTMODE;
        this.response.speak(welcomeMessage).listen(repromptMessage);
        this.emit(':responseReady');
    },
    'SessionEndedRequest': function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
	},
    'AMAZON.HelpIntent'  : function() {
        this.response.speak('Ask me what I can print').listen("I can help you chose a model  ");
        this.emit(':responseReady');
    },
    'AMAZON.CancelIntent': function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    'AMAZON.StopIntent'  : function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    }
};

const ReadModelsHandler = Alexa.CreateStateHandler(states.STARTMODE, {
    'AMAZON.HelpIntent'  : function() {
        this.response.speak('Ask me what I can print').listen("I can help you chose a model  ");
        this.emit(':responseReady');
        this.handler.state = states.CONTINUEMODE;
    },
    'AMAZON.CancelIntent': function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    'AMAZON.StopIntent'  : function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    'AMAZON.YesIntent'   : function() {
        const currentIndex = this.attributes["currentListIndex"];
        var answer = "You can print ";
        var lastIndex = 0;

        if (currentIndex + 5 < allMOdels.length) {
            lastIndex = currentIndex + 5;
        } else {
            lastIndex = allMOdels.length;
        }

        for (var i = currentIndex; i < lastIndex; i++) {
            answer += " a " + allMOdels[i];
            if (i == allMOdels.length - 1) {
                answer += ". And these are all the models available. Do you want me to repeat?";
                this.attributes["currentListIndex"] = 0;
                this.handler.state = states.STARTHELPMODE;
            } else if (i == lastIndex - 1) {
                answer += ". Do you want to hear more?";
                this.attributes["currentListIndex"] += 5;
            } else {
                answer += ", ";
            }
        }

        this.response.speak(answer).listen("");
        this.emit(':responseReady');
    },
    'AMAZON.NoIntent'    : function() {
        this.response.speak('Okay. You can ask me if I can print a specific model. If that is the case, I can connect to your printer and print it if you wish.').listen("");
        this.handler.state = states.CONTINUEMODE;
        this.emit(':responseReady');
    },
    "SpecificModelIntent": function() {
        const model = (this.event.request.intent.slots.model.value).toLowerCase();
        var condition = false;
        for (var i = 0; i < allMOdels.length; i++) {
            if (allMOdels[i].toLowerCase() == model) {
                condition = true;
            }
        }
        var response = "";
        if (condition == true) {
            response = "I am able to print a " + model + ".";
        } else {
            var commons = getListOfCommonWords(model, allMOdels);
            if (commons.length == 0) {
                response = "Unfortunately I do not have this model. However, you can visit replicator.net and add it.";
            } else {
                response = "Unfortunately I do not have this model. However, I have similar models such as ";
                for (var i = 0; i < commons.length; i++) {
                    response = response + commons[i] + " ,";
                }
            }
        }

        this.handler.state = states.CONTINUEMODE;
        this.response.speak(response).listen("");
        this.emit(':responseReady');
    },
    'HowToAddModelIntent': function() {
      this.response.speak('In order to add a model, visit the replicator.azurewebsites.net and follow the instructions').listen("I can help you chose a model  ");
      this.emit(':responseReady');
      this.handler.state = states.CONTINUEMODE;
    }
});

const ReadModelsHelperHandler = Alexa.CreateStateHandler(states.STARTHELPMODE, {
    'AMAZON.HelpIntent'  : function() {
        this.response.speak('Ask me what I can print').listen("I can help you chose a model  ");
        this.emit(':responseReady');
        this.handler.state = states.CONTINUEMODE;
    },
    'AMAZON.CancelIntent': function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    'AMAZON.StopIntent'  : function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    'AMAZON.YesIntent'   : function() {
        this.attributes["currentListIndex"] = 0;
        const currentIndex = this.attributes["currentListIndex"];
        var answer = "You can print ";
        var lastIndex = 0;

        if (currentIndex + 5 < allMOdels.length) {
            lastIndex = currentIndex + 5;
        } else {
            lastIndex = allMOdels.length;
        }

        for (var i = currentIndex; i < lastIndex; i++) {
            answer += " a " + allMOdels[i];
            if (i == allMOdels.length - 1) {
                answer += ". And these are all the models available. Do you want me to repeat?";
                this.attributes["currentListIndex"] = 0;
                this.handler.state = states.STARTMODE;
            } else if (i == lastIndex - 1) {
                answer += ". Do you want to hear more?";
                this.attributes["currentListIndex"] += 5;
            } else {
                answer += ", ";
            }
        }
        this.response.speak(answer).listen("");
        this.handler.state = states.STARTMODE;
        this.emit(':responseReady');
    },
    'AMAZON.NoIntent'    : function() {
        this.response.speak('Okay. You can ask me if I can print a specific model. If that is the case, I can connect to your printer and print it if you wish.').listen("");
        this.handler.state = states.CONTINUEMODE;
        this.emit(':responseReady');
    },
    "SpecificModelIntent": function() {
        const model = (this.event.request.intent.slots.model.value).toLowerCase();
        var condition = false;
        for (var i = 0; i < allMOdels.length; i++) {
            if (allMOdels[i].toLowerCase() == model) {
                condition = true;
            }
        }
        var response = "";
        if (condition == true) {
            response = "I am able to print a " + model + ".";
        } else {
            var commons = getListOfCommonWords(model, allMOdels);
            if (commons.length == 0) {
                response = "Unfortunately I do not have this model. However, you can visit replicator.azurewebsites.net and add it.";
            } else {
                response = "Unfortunately I do not have this model. You can visit replicator.azurewebsites.net and add it. However, I have similar models such as ";
                for (var i = 0; i < commons.length; i++) {
                    response = response + commons[i] + " ,";
                }
            }
        }
        this.handler.state = states.CONTINUEMODE;
        this.response.speak(response).listen("");
        this.emit(':responseReady');
    },
    'HowToAddModelIntent': function() {
      this.response.speak('In order to add a model, visit replicator.azurewebsites.net and follow the instructions').listen("I can help you chose a model  ");
      this.emit(':responseReady');
      this.handler.state = states.CONTINUEMODE;
    },
    'PrintModelIntent' : function() {
      const model = (this.event.request.intent.slots.modelToPrint.value).toLowerCase();
      var response = "";
      var condition = false;
      var modelByInvocationName = "";
      for (var i = 0; i < allMOdels.length; i++) {
          if (allMOdels[i].toLowerCase() == model) {
              modelByInvocationName = allMOdels[i] ;
              condition = true;
          }
      }

      if(condition==true){
        response = "Printing of "+ modelByInvocationName+  " will start shortly" ;
        var request = require("request");
        request('https://replicatorservices.azurewebsites.net/api/TableToQueue?name=' + modelByInvocationName + '&code=rMntaUlGc23ph9o2Mc67TScAUZQNMUV8Xac//lbjY74dqh47X2l8zQ==', function (error, response, body) {
          if (!error && response.statusCode == 200) {
            console.log(body) // Show the HTML for the Google homepage.
          }
        });
      } else {
          var commons = getListOfCommonWords(model, allMOdels);
          if (commons.length == 0) {
              response = "Unfortunately I do not have this model. However, you can visit replicator.azurewebsites.net and add it";
          } else {
              response = "Unfortunately I do not have this model. However, I have similar models such as ";
              for (var i = 0; i < commons.length; i++) {
                  response = response + commons[i] + " ,";
              }
          }
      }

      this.response.speak(response).listen(" Print");
      this.emit(':responseReady');
      this.handler.state = states.CONTINUEMODE;
    }
});

const continueHandler = Alexa.CreateStateHandler(states.CONTINUEMODE, {
    'AMAZON.HelpIntent'  : function() {
        this.response.speak('Ask me what I can print').listen("I can help you chose a model  ");
        this.emit(':responseReady');
    },
    'AMAZON.CancelIntent': function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    'AMAZON.StopIntent'  : function() {
        this.response.speak('Thank you for using The Replicator. Goodbye');
        this.emit(':responseReady');
    },
    "SpecificModelIntent": function() {
        const model = (this.event.request.intent.slots.model.value).toLowerCase();
        var condition = false;
        for (var i = 0; i < allMOdels.length; i++) {
            if (allMOdels[i].toLowerCase() == model) {
                condition = true;
            }
        }
        var response = "";
        if (condition == true) {
            response = "I am able to print a " + model + ".";
        } else {
            var commons = getListOfCommonWords(model, allMOdels);
            if (commons.length == 0) {
                response = "Unfortunately I do not have this model. However, you can visit replicator.azurewebsites.net and add it";
            } else {
                response = "Unfortunately I do not have this model. However, I have similar models such as ";
                for (var i = 0; i < commons.length; i++) {
                    response = response + commons[i] + " ,";
                }
            }
        }
        this.response.speak(response).listen("");
        this.emit(':responseReady');
    },
    "ModelsListIntent"   : function() {
        this.attributes["currentListIndex"] = 0;
        const currentIndex = this.attributes["currentListIndex"];
        var answer = "You can print ";
        var lastIndex = 0;

        if (currentIndex + 5 < allMOdels.length) {
            lastIndex = currentIndex + 5;
        } else {
            lastIndex = allMOdels.length;
        }

        for (var i = currentIndex; i < lastIndex; i++) {
            answer += " a " + allMOdels[i];
            if (i == allMOdels.length - 1) {
                answer += ". These are all the models available";
                this.handler.state = states.CONTINUEMODE;
            } else if (i == lastIndex - 1) {
                answer += ". Do you want to hear more?";
                this.handler.state = states.STARTMODE;
            } else {
                answer += ", ";
            }
        }

        this.attributes["currentListIndex"] += 5;
        this.response.speak(answer).listen("");
        this.emit(':responseReady');
    },
    'HowToAddModelIntent': function() {
      this.response.speak('In order to add a model, visit the replicator.azurewebsites.net and follow the instructions').listen("I can help you chose a model  ");
      this.emit(':responseReady');
      this.handler.state = states.CONTINUEMODE;
    },
    'PrintModelIntent' : function() {
      const model = (this.event.request.intent.slots.modelToPrint.value).toLowerCase();
      var response = "";
      var condition = false;
      var modelByInvocationName = "";
      for (var i = 0; i < allMOdels.length; i++) {
          if (allMOdels[i].toLowerCase() == model) {
              modelByInvocationName = allMOdels[i] ;
              condition = true;
          }
      }

      if(condition==true){
        response = "Printing of "+ modelByInvocationName+  " will start shortly" ;
        var request = require("request");
        request('https://replicatorservices.azurewebsites.net/api/TableToQueue?name=' + modelByInvocationName + '&code=rMntaUlGc23ph9o2Mc67TScAUZQNMUV8Xac//lbjY74dqh47X2l8zQ==', function (error, response, body) {
          if (!error && response.statusCode == 200) {
            console.log(body) // Show the HTML for the Google homepage.
          }
        });
      } else {
          var commons = getListOfCommonWords(model, allMOdels);
          if (commons.length == 0) {
              response = "Unfortunately this model is not available.";
          } else {
              response = "Unfortunately this model is not available. However, I have similar models such as ";
              for (var i = 0; i < commons.length; i++) {
                  response = response + commons[i] + " ,";
              }
          }
      }

      this.response.speak(response).listen(" Print");
      this.emit(':responseReady');
    }
});
