using Microsoft.WindowsAzure.Storage.Table;
using System;
using System.Collections.Generic;
using System.Text;

namespace FunctionApp1
{
    class model : TableEntity
    {
        public string Name { get; set; }
        /*public new string PartitionKey { get; set; }
        public  string Getlist()
        {
            return " " + PartitionKey;
        }*/
        public override string ToString()
        {
            return  " " + Name;
        }
    }
}
