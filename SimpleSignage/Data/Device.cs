#nullable disable
using System.ComponentModel.DataAnnotations;
using System.Collections.Generic;

namespace SimpleSignage.Data
{
    public partial class Device
    {
        public long Id { get; set; }

        [Required]
        public string Name { get; set; }
        public ICollection<Image> Images {  get; set; } = new List<Image>();
    }
}
