using System;
#nullable disable

namespace SimpleSignage.Data
{
    public partial class Image
    {
        public long Id { get; set; }
        public string Description { get; set; }
        public bool Enabled { get; set; }
        public DateTime DateStart { get; set; } = DateTime.Today;
        public DateTime DateEnd { get; set; } = DateTime.Today.AddDays(7);
        public bool Infinite { get; set; }
        public string Filename { get; set; }
        public bool MarkedForDelete { get; set; }
        public DateTime DeleteDate { get; set; }
    }
}
