#nullable disable

namespace SimpleSignage.Data
{
    public partial class ImagesToDevice
    {
        public long Id { get; set; }
        public long? ImageId { get; set; }
        public long? DeviceId { get; set; }
    }
}
