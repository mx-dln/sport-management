from docx import Document
from pathlib import Path

src = Path(r'C:\Users\micha\OneDrive\Desktop\Web Based Project Management Hub\A Web Based Project-Management-Hub-for-Optimized-Extension-Training-Services-at-ISU-Cauayan - Copy.docx')
out = Path(r'C:\Users\micha\OneDrive\Documents\Sites\sports-management\A Web-Based Project Management Hub for Optimized Extension Training Services at ISU-Cauayan - Revised.docx')
d = Document(src)

repls = {
"The general objective of this study is to develop a Web-Based Project Management Hub for Optimized Extension Training Services at ISU-Cauayan to facilitate the management and monitoring of extension programs, projects, project components, extensionists, proposals, and institutional reports.": "The general objective of this study is to develop a Web-Based Project Management Hub for Optimized Extension Training Services at ISU-Cauayan that centralizes and facilitates the management and monitoring of extension programs, projects, project components, extensionists, proposals, accomplishment reports, certificates, supporting documents, notifications, institutional reports, and project reports and certificates.",
"Assess the existing processes and challenges encountered by the Extension and Training Services Office in managing extension programs, projects, project components, proposals, accomplishment reports, certificates, and supporting documents;": "Assess the existing processes and challenges encountered by the Extension and Training Services Office in managing extension programs, projects, project components, proposals, accomplishment reports, certificates, supporting documents, notifications, and reports;",
"To evaluate the effectiveness and reliability of the system's role-based routing and automated notification features in providing timely updates to extensionists and program leaders regarding proposal approvals and project statuses": "Implement role-based routing and automated notifications to provide timely updates to authorized users regarding proposal approvals, project statuses, required actions, and project activities;",
" To evaluate the performance, efficiency, and overall user acceptance of the developed system through feedback from ETS personnel, program leaders, project leaders, and faculty extensionists based on the following ISO/IEC 25010 software quality criteria: ": "Evaluate the developed system through feedback from ETS personnel, program leaders, project leaders, and faculty extensionists based on the following ISO/IEC 25010 software quality characteristics:",
"Functional Suitability ": "Functional Suitability",
"Usability Performance ": "Usability",
"Efficiency s": "Performance Efficiency",
"This chapter presents the requirements analysis, system design, development methodology, testing plan, implementation plan, and evaluation framework for the Web-Based Project Management Hub for Optimized Extension Training Services at Isabela State University�Cauayan Campus.": "This chapter presents the requirements analysis, system design, development methodology, testing plan, implementation plan, and evaluation framework for the Web-Based Project Management Hub for Optimized Extension Training Services at Isabela State University-Cauayan Campus.",
"Design of Software/Systems": "System Design",
"The study identifies authorized users such as the System Administrator, ETS Personnel, Program Leaders, Project Leaders, and Faculty Members participating in extension activities.": "The authorized system users are ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists. University Administration and other faculty members are report beneficiaries and are not regular system users.",
"The authorized users considered in the study are the System Administrator, ETS Personnel, Program Leaders, Project Leaders, and Faculty Members participating in extension activities.": "The authorized system users considered in the study are ETS Personnel, Program Leaders, Project Leaders, and Faculty Extensionists. University Administration and other faculty members are report beneficiaries and are not regular system users.",
"The system will support the management of programs, projects, components, extensionists, proposals, documents, accomplishment reports, certificates, reports and analytics, notifications, audit logs, and user accounts.": "The system will support program management, project management, project component management, extensionist management, proposal management, document management, accomplishment reports, certificates, reports, notifications, audit logs, and user accounts.",
"The functional decomposition diagram divides the system into modules derived from the revised objectives. The modules cover user and role management, programs, projects, project components, proposals, documents, accomplishment reports, notifications, and reports and certificates.": "The functional decomposition diagram divides the system into modules derived from the revised objectives. The modules cover user and role management, programs, projects, project components, extensionist management, proposals, documents, accomplishment reports, notifications, reports and certificates, and audit logs.",
"Figure 3 Existing Procedure Data Flow Diagram ": "Figure 2. Existing Procedure Data Flow Diagram",
"Figure 4 Fishbone Diagram": "Figure 3. Fishbone Diagram",
"Figure 3. Fishbone Diagram of the Existing Extension Project Management Procedure": "Figure 3. Fishbone Diagram",
"Figure 4. Gantt Chart of the Capstone Project": "Figure 4. Gantt Chart",
"Figure 5 Use Case Diagram": "Figure 5. Use Case Diagram",
"Figure 6 Functional Decomposition Diagram": "Figure 6. Functional Decomposition Diagram",
"Figure 7 System Flowchart": "Figure 7. System Flowchart",
"Figure 8 Entity Relationship Diagram": "Figure 8. Entity Relationship Diagram",
"Figure 1. Input-Process-Output (IPO) Diagram of the Web-Based Project Management": "Figure 1. Input-Process-Output Diagram",
}

for p in d.paragraphs:
    if p.text in repls:
        p.text = repls[p.text]
    elif p.text.strip() == 'CHAPTER III':
        p.text = 'CHAPTER III'

# Finalize the continuous figure sequence and remove the duplicate fishbone caption.
seen_fishbone = False
for p in list(d.paragraphs):
    if p.text.strip() == 'Figure 3. Fishbone Diagram':
        if not seen_fishbone:
            seen_fishbone = True
        else:
            p._element.getparent().remove(p._element)
for p in d.paragraphs:
    p.text = p.text.replace('Figure 9 System Architecture', 'Figure 9. System Architecture')
    p.text = p.text.replace('Figure 10 System Implementation and Deployment Flow', 'Figure 10. System Implementation and Deployment Flow')
if not any(p.text.strip() == 'Figure 11. ISO/IEC 25010 Evaluation Framework' for p in d.paragraphs):
    p = d.add_paragraph('Figure 11. ISO/IEC 25010 Evaluation Framework')

# Replace the IPO explanation paragraphs with the audited framework.
ipo = {
"The Input component consists of the information and requirements needed for the development and operation of the proposed system.": "Input: Project Data and Requirements. The input consists of user and role information; program information; project and project component information; proposals and approval requirements; supporting documents; accomplishment reports; certificate information; notification and reporting requirements; and user feedback and evaluation responses.",
"The Process component represents the activities performed by the proposed system in transforming the collected information into organized and useful project-management information.": "Process: System Methods and Activities. The process consists of authentication and role verification; data input and validation; proposal submission and approval routing; document storage and retrieval; project and component monitoring; accomplishment report verification; notification delivery; report and certificate generation; activity logging; and ISO/IEC 25010 evaluation.",
"The Output component represents the information, records, and services generated by the proposed Web-Based Project Management Hub after the inputs have been processed.": "Output: Information and System Outputs. The outputs consist of program and project records; project status and component progress; approved or returned proposals; organized documents; verified accomplishment reports; certificates; notifications; institutional and project reports; and evaluation results with recommended improvements.",
}
for p in d.paragraphs:
    for old,new in ipo.items():
        if p.text.startswith(old):
            p.text = new

# Remove researcher-facing template notes while preserving substantive manuscript text.
bad_starts = ('For tables, use the following format', 'Table 1. From References > Insert Caption', 'Remember', 'The final scale should be aligned', 'Notes intended for the researchers')
for p in list(d.paragraphs):
    if p.text.strip().startswith(bad_starts):
        p._element.getparent().remove(p._element)

# Normalize recurring terminology in prose without changing the template styling.
for p in d.paragraphs:
    if p.text:
        txt = p.text.replace('ISU�Cauayan', 'ISU-Cauayan').replace('Faculty Members and Extensionists', 'Faculty Extensionists')
        if txt != p.text:
            p.text = txt

d.save(out)
print(out)
