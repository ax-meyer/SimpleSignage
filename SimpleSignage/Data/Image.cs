using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
#nullable disable

namespace SimpleSignage.Data
{
    public partial class Image
    {
        public long Id { get; set; }

        [StringLength(100, ErrorMessage = "Description too long (100 character limit).")]
        public string Description { get; set; }
        public bool Enabled { get; set; }
        
        [Required]
        public DateTime DateStart { get; set; } = DateTime.Today;

        [Required]
        public DateTime DateEnd { get; set; } = DateTime.Today.AddDays(7);
        public bool Infinite { get; set; }
        
        public string Filename { get; set; }
        public bool MarkedForDelete { get; set; }
        public DateTime DeleteDate { get; set; }
        public ICollection<Device> Devices { get; set; } = new List<Device>();
    }
}
