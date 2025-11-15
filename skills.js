//Francis Charles Mbayo

document.addEventListener('DOMContentLoaded', function() {
    const skillsData = {
        "Frontend Technologies": [
            { name: "HTML5", level: 95 },
            { name: "CSS3/SCSS", level: 90 },
            { name: "JavaScript", level: 88 },
            { name: "React", level: 85 },
            { name: "Vue.js", level: 75 },
            { name: "TypeScript", level: 70 }
        ],
        "Backend Technologies": [
            { name: "Node.js", level: 80 },
            { name: "Express", level: 75 },
            { name: "Python", level: 70 },
            { name: "MongoDB", level: 65 },
            { name: "MySQL", level: 60 }
        ],
        "Tools & Others": [
            { name: "Git/GitHub", level: 90 },
            { name: "Webpack", level: 75 },
            { name: "Docker", level: 60 },
            { name: "AWS", level: 55 },
            { name: "Figma", level: 80 }
        ]
    };

    const additionalSkills = [
        "Responsive Design", "RESTful APIs", "Agile Methodology", "UI/UX Principles",
        "Cross-browser Compatibility", "Performance Optimization", "Testing & Debugging",
        "CI/CD", "Mobile First Development", "Accessibility Standards"
    ];

    const skillsContainer = document.getElementById('skills-container');
    
    if (skillsContainer) {
        let skillsHTML = '';
        
        // Create skill category cards
        for (const [category, skills] of Object.entries(skillsData)) {
            skillsHTML += `
                <div class="skill-category">
                    <h3>${category}</h3>
                    ${skills.map(skill => `
                        <div class="skill-item">
                            <div class="skill-name">
                                <span>${skill.name}</span>
                                <span>${skill.level}%</span>
                            </div>
                            <div class="skill-bar">
                                <div class="skill-level" data-level="${skill.level}"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        // Add additional skills category
        skillsHTML += `
            <div class="skill-category">
                <h3>Additional Skills</h3>
                <div class="skill-tags">
                    ${additionalSkills.map(skill => `
                        <span class="skill-tag">${skill}</span>
                    `).join('')}
                </div>
            </div>
        `;
        
        skillsContainer.innerHTML = skillsHTML;
        
        // Animate skill bars
        const skillLevels = document.querySelectorAll('.skill-level');
        skillLevels.forEach(bar => {
            const level = bar.getAttribute('data-level');
            setTimeout(() => {
                bar.style.width = `${level}%`;
            }, 100);
        });
        
        // Add skills legend
        const legend = document.createElement('div');
        legend.className = 'skills-legend';
        legend.innerHTML = `
            <div class="legend-item">
                <div class="legend-color legend-beginner"></div>
                <span>Beginner (0-40%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-intermediate"></div>
                <span>Intermediate (41-70%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-advanced"></div>
                <span>Advanced (71-85%)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-expert"></div>
                <span>Expert (86-100%)</span>
            </div>
        `;
        
        skillsContainer.appendChild(legend);
    }
});