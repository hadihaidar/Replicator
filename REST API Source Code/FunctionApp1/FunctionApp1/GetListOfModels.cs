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
using System.Threading;
using System.Collections;
using System.Collections.Generic;
namespace FunctionApp1{
    public static class GetListOfModels{
        [FunctionName("GetListOfModels")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Function, "get", "post", Route = null)] HttpRequest req,ILogger log){
            log.LogInformation("C# HTTP trigger function processed a request.");
            string requestBody = await new StreamReader(req.Body).ReadToEndAsync();
            var connectionString = "connectionString";
            CloudStorageAccount storageAccount = CloudStorageAccount.Parse(connectionString);
            CloudTableClient tableClient = storageAccount.CreateCloudTableClient();
            CloudTable table = tableClient.GetTableReference("models");
            TableQuery<model> query = new TableQuery<model>().Where(TableQuery.GenerateFilterCondition("PartitionKey", QueryComparisons.Equal, "1"));
            var alerts = new List<model>();
            TableContinuationToken continuationToken = null;
            do
            {
                var page = await table.ExecuteQuerySegmentedAsync(query, continuationToken);
                continuationToken = page.ContinuationToken;
                alerts.AddRange(page.Results);
            }
            while (continuationToken != null);
            string x = "[";
            foreach (model m in alerts) {
                x += "{\"name\":" + "\"" + m.RowKey + "\"" + "},\n";
            }
            x = x.Substring(0, x.Length - 2) + "]";
            return (ActionResult)new OkObjectResult(x);
        }
    }
}