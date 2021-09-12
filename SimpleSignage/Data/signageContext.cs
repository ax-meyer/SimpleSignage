using System;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata;

#nullable disable

namespace SimpleSignage.Data
{
    public partial class signageContext : DbContext
    {
        /*public signageContext()
        {
        }*/

        public signageContext(DbContextOptions<signageContext> options)
            : base(options)
        {
        }

        public virtual DbSet<Cleanup> Cleanups { get; set; }
        public virtual DbSet<Device> Devices { get; set; }
        public virtual DbSet<Image> Images { get; set; }
        public virtual DbSet<ImagesToDevice> ImagesToDevices { get; set; }

        protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
        {
            if (!optionsBuilder.IsConfigured)
            {

            }
        }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            modelBuilder.Entity<Cleanup>(entity =>
            {
                entity.ToTable("CLEANUPS");

                entity.Property(e => e.Id).HasColumnName("ID");

                entity.Property(e => e.Date).HasColumnName("DATE");

                entity.Property(e => e.DeletedImages).HasColumnName("DELETED_IMAGES");
            });

            modelBuilder.Entity<Device>(entity =>
            {
                entity.ToTable("DEVICES");

                entity.Property(e => e.Id).HasColumnName("ID");

                entity.Property(e => e.Name).HasColumnName("NAME");
            });

            modelBuilder.Entity<Image>(entity =>
            {
                entity.ToTable("IMAGES");

                entity.Property(e => e.Id).HasColumnName("ID");

                entity.Property(e => e.DateEnd).HasColumnName("DATE_END");

                entity.Property(e => e.DateStart).HasColumnName("DATE_START");

                entity.Property(e => e.DeleteDate).HasColumnName("DELETE_DATE");

                entity.Property(e => e.Description).HasColumnName("DESCRIPTION");

                entity.Property(e => e.Enabled)
                    .HasColumnName("ENABLED")
                    .HasDefaultValueSql("1");

                entity.Property(e => e.Filename).HasColumnName("FILENAME");

                entity.Property(e => e.Infinite)
                    .HasColumnName("INFINITE")
                    .HasDefaultValueSql("0");

                entity.Property(e => e.MarkedForDelete)
                    .HasColumnName("MARKED_FOR_DELETE")
                    .HasDefaultValueSql("0");
            });

            modelBuilder.Entity<ImagesToDevice>(entity =>
            {
                entity.ToTable("IMAGES_TO_DEVICES");

                entity.Property(e => e.Id).HasColumnName("ID");

                entity.Property(e => e.DeviceId).HasColumnName("DEVICE_ID");

                entity.Property(e => e.ImageId).HasColumnName("IMAGE_ID");
            });

            OnModelCreatingPartial(modelBuilder);
        }

        partial void OnModelCreatingPartial(ModelBuilder modelBuilder);
    }
}
