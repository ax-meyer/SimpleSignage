using System;
using System.Linq;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.DependencyInjection;

namespace SimpleSignage.Data
{
    public class DatabaseService
    {
        public static void Initialize(IServiceProvider serviceProvider)
        {
            
            using (var context = new signageContext(
                serviceProvider.GetRequiredService<
                    DbContextOptions<signageContext>>()))
            {
                context.Database.EnsureCreated();
                // Look for any movies.
                if (!context.Images.Any())
                {


                    context.Images.AddRange(
                        new Image
                        {
                            Description = "Test 1",
                            Enabled = false,
                            DateStart = DateTime.Now,
                        },

                        new Image
                        {
                            Description = "Test 2",
                            Enabled = true,
                        },

                        new Image
                        {
                            Description = "Test 3",
                        }
                    );
                }

                if (!context.Devices.Any())
                {


                    context.Devices.AddRange(
                        new Device
                        {
                            Name = "Küche"
                        },

                        new Device
                        {
                            Name = "Büro"
                        }
                    );
                }
                context.SaveChanges();
            }
        }
    }
}