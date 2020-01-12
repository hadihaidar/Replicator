using System;
using System.IO;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Extensions.Http;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Newtonsoft.Json;
using Microsoft.WindowsAzure.Storage;
using Microsoft.WindowsAzure.Storage.Table;
using System.Collections.Generic;

namespace FunctionApp1
{
    public static class PostToTable
    {
        [FunctionName("postToTable")]
        public static IActionResult Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,
            ILogger log)
        {
            log.LogInformation("C# HTTP trigger function processed a request.");
            string name = req.Query["name"];
            string url = req.Query["url"];
            var connectionString = "connectionString";
            CloudStorageAccount storageAccount = CloudStorageAccount.Parse(connectionString);
            CloudTableClient tableClient = storageAccount.CreateCloudTableClient();
            CloudTable table = tableClient.GetTableReference("models");
            model model1 = new model();
            model1.RowKey = name;
            model1.Name = url;
            model1.PartitionKey = "1";
            TableOperation insertOperation = TableOperation.Insert(model1);
            table.ExecuteAsync(insertOperation);
            return (ActionResult)new OkObjectResult("added");

        }
    }
}