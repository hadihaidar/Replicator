using System;
using System.IO;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Extensions.Http;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Newtonsoft.Json;
using Microsoft.WindowsAzure.Storage.Table;
using Microsoft.WindowsAzure.Storage;
using Microsoft.WindowsAzure.Storage.Queue;

namespace FunctionApp1{
    public static class Function1{
        [FunctionName("TableToQueue")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log){
            log.LogInformation("C# HTTP trigger function processed a request.");
            string name = req.Query["name"];
            string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
            dynamic data = JsonConvert.DeserializeObject(requestBody);
            name = name ?? data?.name;
            var connectionString = "connection string";
            CloudStorageAccount storageAccount = CloudStorageAccount.Parse(connectionString);
            CloudTableClient tableClient = storageAccount.CreateCloudTableClient();
            CloudTable table = tableClient.GetTableReference("models");
            try{
                var x = await GetAllMessages(table, name);
                string url = ((model)x.Result).ToString();
/////////////// add a message to the queue//////////////////////////////////////////////

                CloudQueueClient queueClient = storageAccount.CreateCloudQueueClient();
                CloudQueue queue = queueClient.GetQueueReference("printjobs");
                var message = new CloudQueueMessage(url);
                await queue.AddMessageAsync(message);
/////////////////////////////////////////////////////////////////////////////////////////
                return name != null
                    ? (ActionResult)new OkObjectResult("{\"printing\": true}")
                    : new BadRequestObjectResult("Please pass a name on the query string or in the request body");
            }
            catch (Exception e){
                return (ActionResult)new OkObjectResult("{\"printing\":false}");
            }
        }
        static async Task<TableResult>  GetAllMessages(CloudTable table, String InvocationName){
            TableResult x = await table.ExecuteAsync(TableOperation.Retrieve<model>("1", InvocationName ));
            return x;
        }
    }
}