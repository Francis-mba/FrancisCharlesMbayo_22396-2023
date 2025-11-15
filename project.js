//Francis Charles Mbayo


document.addEventListener('DOMContentLoaded', function() {
    const projectsData = [
        {
            title: "E-Commerce Platform",
            description: "A full-featured e-commerce platform built with React and Node.js. Features include user authentication, product catalog, shopping cart, and payment integration.",
            tools: ["PHP", "Mysql", "html", "css"],
            demoLink: "https://francis-mba.github.io/FrancisCharlesMbayo_22396-2023/",
            codeLink: "https://github.com/Francis-mba/FrancisCharlesMbayo_22396-2023.git",
            icon: "fas fa-shopping-cart"
        },
        {
            title: "Rock paper n scissors ",
            description: "an interactive game computer vs human.",
            tools: [ "js", "css", "html"],
            demoLink: "https://francis-mba.github.io/FrancisCharlesMbayo_22396-2023/",
            codeLink: "https://github.com/Francis-mba/FrancisCharlesMbayo_22396-2023.git",
            icon: "fas fa-game"
        },
    
    ];

    const projectsContainer = document.getElementById('projects-container');
    
    if (projectsContainer) {
        projectsContainer.innerHTML = projectsData.map(project => `
            <div class="project-card">
                <div class="project-img">
                    <i class="${project.icon}"></i>
                </div>
                <div class="project-info">
                    <h3>${project.title}</h3>
                    <p class="project-description">${project.description}</p>
                    <div class="project-tools">
                        ${project.tools.map(tool => `<span class="tool">${tool}</span>`).join('')}
                    </div>
                    <div class="project-links">
                        <a href="${project.demoLink}" class="project-link">
                            <i class="fas fa-external-link-alt">https://francis-mba.github.io/FrancisCharlesMbayo_22396-2023/</i>
                            Live Demo
                        </a>
                        <a href="${project.codeLink}" class="project-link outline">
                            <i class="fab fa-github">https://github.com/Francis-mba/FrancisCharlesMbayo_22396-2023/tree/main</i>
                            View Code
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    }
});
