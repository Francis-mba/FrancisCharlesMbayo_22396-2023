 /*Francis Charles Mbayo*/

document.addEventListener('DOMContentLoaded', function() {
    const certificatesData = [
        {
            title: "Advanced JavaScript Certification",
            issuer: "CodeMaster Institute",
            date: "June 2023",
            credentialId: "CM-JS-2023-001",
            icon: "fab fa-js-square",
            link: "certificates/certificate1.html"
        },
        {
            title: "React Developer Certification",
            issuer: "React Masters",
            date: "March 2023",
            credentialId: "RM-RCT-2023-042",
            icon: "fab fa-react",
            link: "certificates/certificate2.html"
        },
        {
            title: "AWS Cloud Practitioner",
            issuer: "Amazon Web Services",
            date: "January 2023",
            credentialId: "AWS-CP-12345",
            icon: "fab fa-aws",
            link: "certificates/certificate3.html"
        },
        {
            title: "UI/UX Design Specialization",
            issuer: "Google Career Certificates",
            date: "November 2022",
            credentialId: "GOOG-UX-2022-789",
            icon: "fas fa-palette",
            link: "certificates/certificate4.html"
        },
        {
            title: "Full Stack Development",
            issuer: "freeCodeCamp",
            date: "August 2022",
            credentialId: "FCC-FSD-2022-456",
            icon: "fas fa-code",
            link: "certificates/certificate5.html"
        },
        {
            title: "Best Project Award 2023",
            issuer: "Tech Innovation Summit",
            date: "December 2023",
            credentialId: "TIS-AWD-2023-001",
            icon: "fas fa-trophy",
            link: "#"
        }
    ];

    const certificatesContainer = document.getElementById('certificates-container');
    
    if (certificatesContainer) {
        certificatesContainer.innerHTML = certificatesData.map(cert => `
            <div class="certificate-card">
                <div class="certificate-badge">
                    <i class="${cert.icon}"></i>
                </div>
                <h3>${cert.title}</h3>
                <span class="issuer">${cert.issuer}</span>
                <span class="date">${cert.date}</span>
                <div class="certificate-actions">
                    <a href="${cert.link}" class="certificate-link">
                        <i class="fas fa-eye"></i>
                        View Details
                    </a>
                    ${cert.credentialId ? `
                    <a href="#" class="certificate-link outline">
                        <i class="fas fa-shield-alt"></i>
                        Verify
                    </a>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }
});