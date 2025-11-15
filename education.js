 /*Francis Charles Mbayo*/

document.addEventListener('DOMContentLoaded', function() {
    const educationData = [
        {
            degree: "Bachelor of Science in Information System and Management",
            institution: "UniLAK",
            duration: "2020 - 2024",
            description: "Graduated with honors. Focused on software engineering, web development, and human-computer interaction.",
            gpa: "3.6"
        },
        {
            degree: "Frontend Development Bootcamp",
            institution: "CodeMaster Institute",
            duration: "2019 - 2020",
            description: "Intensive 6-month program covering modern frontend technologies including React, Vue, and advanced CSS.",
            gpa: "Certificate of Excellence"
        },
        {
            degree: "High School Diploma",
            institution: "Noah's Ark High School",
            duration: "2009 - 2022",
            description: "Focus on mathematics and computer science.",
            gpa: "3.7"
        }
    ];

    const educationContainer = document.getElementById('education-container');
    
    if (educationContainer) {
        educationContainer.innerHTML = `
            <div class="timeline">
                ${educationData.map((edu, index) => `
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <h3>${edu.degree}</h3>
                            <div class="timeline-date">${edu.duration}</div>
                            <div class="timeline-institution">${edu.institution}</div>
                            <p>${edu.description}</p>
                            <div class="timeline-gpa"><strong>GPA/Result:</strong> ${edu.gpa}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
});