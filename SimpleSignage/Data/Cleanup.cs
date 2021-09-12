using System;
#nullable disable

namespace SimpleSignage.Data
{
    public partial class Cleanup
    {
        public long Id { get; set; }
        public DateTime Date { get; set; }
        public long? DeletedImages { get; set; }
    }
}
