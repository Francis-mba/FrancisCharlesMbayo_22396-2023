 /*Francis Charles Mbayo*/

document.addEventListener('DOMContentLoaded', function() {
    const experienceData = [
        {
            title: "Frontend Developer",
            company: "Tech Solutions Inc.",
            duration: "2022 - Present",
            location: "San Francisco, CA",
            responsibilities: [
                "Developed responsive web applications using React, TypeScript, and Sass",
                "Collaborated with UX designers to implement pixel-perfect designs",
                "Optimized application performance, reducing load time by 40%",
                "Mentored junior developers and conducted code reviews"
            ]
        },
        {
            title: "Web Development Intern",
            company: "Digital Creations Agency",
            duration: "2021 - 2022",
            location: "Remote",
            responsibilities: [
                "Built and maintained client websites using HTML, CSS, and JavaScript",
                "Implemented responsive designs for mobile and tablet devices",
                "Assisted in debugging and troubleshooting frontend issues",
                "Participated in agile development sprints and team meetings"
            ]
        },
        {
            title: "Freelance Web Developer",
            company: "Self-Employed",
            duration: "2020 - 2021",
            location: "Remote",
            responsibilities: [
                "Created custom websites for small businesses and startups",
                "Managed client projects from conception to deployment",
                "Provided ongoing maintenance and support services",
                "Built e-commerce solutions using WordPress and Shopify"
            ]
        }
    ];

    const experienceContainer = document.getElementById('experience-container');
    
    if (experienceContainer) {
        experienceContainer.innerHTML = `
            <div class="experience-grid">
                ${experienceData.map(exp => `
                    <div class="experience-card">
                        <div class="experience-header">
                            <div>
                                <h3 class="experience-title">${exp.title}</h3>
                                <div class="experience-company">${exp.company} • ${exp.location}</div>
                            </div>
                            <div class="experience-date">${exp.duration}</div>
                        </div>
                        <div class="experience-responsibilities">
                            <ul>
                                ${exp.responsibilities.map(resp => `<li>${resp}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
});