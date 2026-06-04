# -*- coding: utf-8 -*-
"""Generate Defrilex Academy knowledge sources inventory PDF."""

from fpdf import FPDF
from datetime import date

OUTPUT = r"c:\laragon\www\academy (1)\docs\Defrilex-Academy-Inventaire-Sources-Knowledge.pdf"

SOURCES = [
    ("Defrilex SOPs & Playbooks", "(interne)", "Interne", "Tous", "Interne", "Oui", "Haute", "10"),
    ("Defrilex QA Scorecards", "(interne)", "Interne", "Tous", "Interne", "Oui", "Haute", "10"),
    ("Defrilex Coaching & Reviews", "(interne)", "Interne", "Tous", "Interne", "Oui", "Haute", "10"),
    ("NCIHC Standards of Practice", "https://www.ncihc.org/", "Standard", "Interpretes", "Gratuit", "Oui", "Haute", "10"),
    ("NCIHC Code of Ethics", "https://www.ncihc.org/", "Standard", "Interpretes", "Gratuit", "Oui", "Haute", "10"),
    ("CHIA Healthcare Interpreter Standards", "https://www.chiaonline.org/CHIA-Standards", "Standard", "Interpretes", "Gratuit", "Oui", "Haute", "10"),
    ("IMIA Medical Interpreting Standards", "http://www.imiaweb.org/standards/", "Standard", "Interpretes", "Gratuit", "Oui", "Haute", "9"),
    ("NAJIT Code of Ethics", "https://najit.org/", "Standard", "Interpretation juridique", "Gratuit", "Oui", "Haute", "10"),
    ("ASTM F2089-24", "https://www.astm.org/f2089-24.html", "Standard", "Interpretes", "Payant (~72 USD)", "Licence", "Moyenne", "9"),
    ("ISO 21998:2020", "https://www.iso.org/standard/72344.html", "Standard", "Interpretation sante", "Payant", "Licence", "Moyenne", "9"),
    ("CCHI Certification", "https://cchicertification.org/", "Organisme cert.", "Interpretes sante", "Mixte", "Reference", "Moyenne", "8"),
    ("NBCMI / Hub-CMI", "https://www.certifiedmedicalinterpreters.org/", "Organisme cert.", "Interpretes medicaux", "Mixte", "Reference", "Moyenne", "8"),
    ("UMLS Metathesaurus (NLM)", "https://www.nlm.nih.gov/databases/umls.html", "API / Terminologie", "Interpretes medicaux", "Gratuit", "Oui*", "Haute", "9"),
    ("PubMed / NCBI E-utilities", "https://www.ncbi.nlm.nih.gov/books/NBK25497/", "API / Recherche", "Medical / QA", "Gratuit", "Oui*", "Moyenne", "7"),
    ("LexPredict Legal Dictionary", "https://github.com/LexPredict/lexpredict-legal-dictionary", "Donnees ouvertes", "Interpretation juridique", "Gratuit CC BY-SA", "Oui", "Moyenne", "7"),
    ("Merriam-Webster Dictionary API", "https://dictionaryapi.com/", "API", "Langue / CS", "Gratuit / payant", "Licence", "Basse", "6"),
    ("O*NET Web Services", "https://services.onetcenter.org/", "API", "Tous roles", "Gratuit", "Oui*", "Haute", "9"),
    ("ICMI Training", "https://www.icmi.com/training", "Fournisseur payant", "Service client", "Payant", "Contrat", "Haute", "9"),
    ("CXPA CCXP / Book of Knowledge", "https://cxpaglobal.org/get-certified", "Organisme cert.", "CX / CS", "Mixte", "Licence", "Haute", "9"),
    ("CCXP Candidate Handbook", "https://www.prometric.com/exams/ccxp", "Organisme cert.", "CX", "Reference", "Partiel", "Moyenne", "8"),
    ("HubSpot Academy", "https://legal.hubspot.com/education-partner-agreement", "Fournisseur", "Marketing", "Gratuit", "Non commercial", "Basse", "5"),
    ("Google Skillshop", "https://skillshop.withgoogle.com/", "Fournisseur", "Marketing", "Gratuit", "Liens seulement", "Moyenne", "6"),
    ("Meta Blueprint", "https://www.facebook.com/business/learn", "Fournisseur", "Marketing", "Gratuit", "Liens seulement", "Basse", "5"),
    ("SEMrush Academy", "https://www.semrush.com/academy/", "Fournisseur", "Marketing SEO", "Gratuit", "Verifier ToS", "Moyenne", "6"),
    ("SHRM BASK", "https://www.shrm.org/credentials/certification/exam-preparation/bask", "Standard", "RH / Management", "Mixte", "Licence", "Moyenne", "8"),
    ("ATD Capability Model", "https://www.td.org/", "Organisation L&D", "L&D / Tous", "Mixte", "Enterprise", "Haute", "9"),
    ("Kirkpatrick Model", "https://www.kirkpatrickpartners.com/", "Cadre", "L&D / QA", "Mixte", "Licence", "Haute", "8"),
    ("ADDIE / Bloom (UIC / OER)", "https://teaching.uic.edu/", "Academique / OER", "Conception pedagogique", "Gratuit", "Oui", "Haute", "9"),
    ("OER Commons", "https://oercommons.org/", "OER + API", "Creation cours", "Gratuit", "CC-BY filtre", "Haute", "8"),
    ("OpenStax", "https://openstax.org/", "OER", "Business / sciences", "Gratuit NC", "Permission requise", "Basse", "6"),
    ("Skillsoft Percipio", "https://documentation.skillsoft.com/", "LMS commercial", "Tous", "Payant", "Contrat + API", "Haute", "9"),
    ("LinkedIn Learning", "https://learn.microsoft.com/linkedin/learning/", "LMS commercial", "Tous", "Payant", "Contrat + API", "Haute", "9"),
    ("Coursera for Business", "https://www.coursera.org/business/integrations", "LMS commercial", "Tous", "Payant", "Contrat + API", "Moyenne", "9"),
    ("Sandler Training", "https://sandler.com/enterprise/", "Vendeur ventes", "Ventes", "Payant", "Contrat SCORM", "Haute", "9"),
    ("Force Management", "https://www.forcemanagement.com/", "Vendeur ventes", "Ventes", "Payant", "Sur mesure", "Moyenne", "8"),
    ("MEDDIC Academy", "https://meddic.academy/", "Vendeur ventes", "Ventes", "Payant", "Licence", "Moyenne", "8"),
    ("Gong Revenue AI", "https://www.gong.io/", "Plateforme CI", "Ventes", "Payant", "API", "Haute", "9"),
    ("OSHA Teaching Aids", "https://www.osha.gov/training/outreach/teaching-aids", "Gouvernement", "Operations", "Gratuit", "Oui", "Basse", "6"),
    ("SCORM / xAPI", "https://scorm.com/scorm-explained/", "Standard", "LMS", "Gratuit spec", "Oui", "Haute", "9"),
    ("Docebo (reference)", "https://developer.docebo.com/", "Plateforme", "L&D tech", "Payant", "Reference", "Basse", "6"),
    ("Degreed API", "https://developer.degreed.com/", "API LXP", "Competences", "Payant", "Contrat", "Moyenne", "7"),
    ("Google Ads API", "https://developers.google.com/google-ads/api/", "API", "Marketing ops", "Gratuit", "Gestion ads", "Basse", "5"),
    ("FindLaw Legal Dictionary", "https://www.findlaw.com/", "Reference", "Juridique", "Gratuit web", "Liens", "Basse", "6"),
    ("Winning by Design SPICED", "https://www.winningbydesign.com/", "Cadre ventes", "Ventes", "Payant", "Licence", "Moyenne", "8"),
    ("MasterWord / Bridge", "https://masterword.institute/", "Formation", "Interpretes", "Payant", "Partenaire", "Moyenne", "7"),
    ("InterpreterEd.com", "https://interpretered.com/", "Formation", "Interpretes", "Payant", "Partenaire", "Moyenne", "7"),
    ("ERIC", "https://eric.ed.gov/", "Recherche", "L&D / CS", "Gratuit", "Metadata", "Moyenne", "6"),
    ("NIST AI RMF", "https://www.nist.gov/itl/ai-risk-management-framework", "Gouvernance IA", "Gouvernance", "Gratuit", "Oui", "Moyenne", "7"),
]

APIS = [
    ("O*NET Web Services", "https://services.onetcenter.org/", "Competences, taches, KSA par metier", "Gratuit", "Haute"),
    ("UMLS UTS", "https://uts.nlm.nih.gov/", "Terminologie medicale", "Gratuit (licence)", "Haute"),
    ("OER Commons API", "http://docs.oercommons.org/api/", "Metadonnees OER", "Gratuit", "Haute"),
    ("NCBI E-utilities", "https://www.ncbi.nlm.nih.gov/books/NBK25497/", "PubMed / recherche", "Gratuit", "Moyenne"),
    ("Percipio API", "https://api.percipio.com/", "Catalogue, completions", "Enterprise", "Haute"),
    ("LinkedIn Learning API", "https://learn.microsoft.com/linkedin/learning/", "Catalogue licencie", "Site license", "Haute"),
    ("Coursera for Business", "https://www.coursera.org/business/integrations", "Sync catalogue OAuth", "Enterprise", "Moyenne"),
    ("Gong API", "https://api.gong.io/v2/", "Appels, transcripts, coaching", "Payant", "Haute"),
    ("Degreed API", "https://developer.degreed.com/", "Taxonomie competences", "Enterprise", "Moyenne"),
    ("Merriam-Webster API", "https://dictionaryapi.com/", "Definitions", "Gratuit / commercial", "Basse"),
    ("LexPredict Legal Dict", "https://github.com/LexPredict/lexpredict-legal-dictionary", "Termes juridiques JSON", "Gratuit CC BY-SA", "Moyenne"),
]


class PDF(FPDF):
    def header(self):
        if self.page_no() > 1:
            self.set_font("Helvetica", "I", 8)
            self.set_text_color(100, 100, 100)
            self.cell(0, 8, "Defrilex Academy - Inventaire des sources de connaissances", align="C")
            self.ln(10)

    def footer(self):
        self.set_y(-15)
        self.set_font("Helvetica", "I", 8)
        self.set_text_color(120, 120, 120)
        self.cell(0, 10, f"Page {self.page_no()}", align="C")


def safe(text: str) -> str:
    return text.encode("latin-1", "replace").decode("latin-1")


def main():
    pdf = PDF(orientation="L", unit="mm", format="A4")
    pdf.set_auto_page_break(auto=True, margin=15)
    pdf.add_page()

    # Cover
    pdf.set_font("Helvetica", "B", 22)
    pdf.set_text_color(30, 60, 120)
    pdf.ln(25)
    pdf.cell(0, 12, safe("Defrilex Academy"), align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.set_font("Helvetica", "B", 16)
    pdf.set_text_color(50, 50, 50)
    pdf.cell(0, 10, safe("Inventaire des sources de connaissances"), align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(4)
    pdf.set_font("Helvetica", "", 12)
    pdf.cell(0, 8, safe("Coach Assistant & Course Creation Assistant"), align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.ln(8)
    pdf.set_font("Helvetica", "I", 10)
    pdf.set_text_color(80, 80, 80)
    pdf.cell(0, 6, safe(f"Document genere le {date.today().strftime('%d/%m/%Y')}"), align="C", new_x="LMARGIN", new_y="NEXT")
    pdf.cell(0, 6, safe(f"{len(SOURCES)} sources | {len(APIS)} integrations API"), align="C", new_x="LMARGIN", new_y="NEXT")

    pdf.ln(20)
    pdf.set_font("Helvetica", "", 10)
    pdf.set_text_color(40, 40, 40)
    intro = (
        "Ce document liste les sources externes et internes recommandees pour alimenter "
        "les assistants IA Defrilex Academy (coaching en direct et creation de cours). "
        "Priorite : sources fiables, utilisables legalement en entreprise, structurees pour RAG. "
        "* = conditions de licence specifiques (voir rapport complet)."
    )
    pdf.multi_cell(0, 6, safe(intro), align="C")

    # Legend
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 14)
    pdf.set_text_color(30, 60, 120)
    pdf.cell(0, 10, safe("Legende"), new_x="LMARGIN", new_y="NEXT")
    pdf.ln(2)
    pdf.set_font("Helvetica", "", 9)
    pdf.set_text_color(40, 40, 40)
    for line in [
        "Priorite Haute : MVP et fondation enterprise",
        "Priorite Moyenne : phase 2-3",
        "Priorite Basse : complementaire ou restrictions fortes",
        "Utilite : LC=coaching live | CC=cours | AQ=quiz | RP=role-play | CT=certification",
        "Ne pas scraper du contenu protege ; toujours citer les sources approuvees.",
    ]:
        pdf.cell(0, 6, safe(line), new_x="LMARGIN", new_y="NEXT")

    # Main table
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 14)
    pdf.cell(0, 10, safe("Table 1 - Inventaire des sources (45)"), new_x="LMARGIN", new_y="NEXT")
    pdf.ln(2)

    col_w = [8, 52, 38, 22, 28, 22, 18, 14, 10]
    headers = ["#", "Source", "URL", "Categorie", "Domaine", "Cout", "Commercial", "Priorite", "Qual."]

    def table_header():
        pdf.set_font("Helvetica", "B", 7)
        pdf.set_fill_color(30, 60, 120)
        pdf.set_text_color(255, 255, 255)
        for i, h in enumerate(headers):
            pdf.cell(col_w[i], 7, safe(h), border=1, fill=True, align="C")
        pdf.ln()

    table_header()
    pdf.set_font("Helvetica", "", 6.5)
    pdf.set_text_color(30, 30, 30)

    for idx, row in enumerate(SOURCES, 1):
        if pdf.get_y() > 185:
            pdf.add_page()
            table_header()
            pdf.set_font("Helvetica", "", 6.5)

        fill = idx % 2 == 0
        if fill:
            pdf.set_fill_color(245, 247, 250)
        data = [str(idx)] + list(row)
        aligns = ["C", "L", "L", "L", "L", "C", "C", "C", "C"]
        for i, cell in enumerate(data):
            pdf.cell(col_w[i], 6, safe(str(cell)[:80]), border=1, fill=fill, align=aligns[i])
        pdf.ln()

    # APIs table
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 14)
    pdf.cell(0, 10, safe("Table 2 - APIs et integrations"), new_x="LMARGIN", new_y="NEXT")
    pdf.ln(2)

    api_w = [45, 55, 75, 35, 25]
    api_h = ["API / Plateforme", "URL", "Donnees", "Acces", "Priorite"]

    pdf.set_font("Helvetica", "B", 8)
    pdf.set_fill_color(30, 60, 120)
    pdf.set_text_color(255, 255, 255)
    for i, h in enumerate(api_h):
        pdf.cell(api_w[i], 8, safe(h), border=1, fill=True, align="C")
    pdf.ln()

    pdf.set_font("Helvetica", "", 7)
    pdf.set_text_color(30, 30, 30)
    for n, row in enumerate(APIS, 1):
        fill = n % 2 == 0
        if fill:
            pdf.set_fill_color(245, 247, 250)
        for i, cell in enumerate(row):
            pdf.cell(api_w[i], 7, safe(str(cell)[:90]), border=1, fill=fill, align="L" if i > 0 else "L")
        pdf.ln()

    # Top 10
    pdf.add_page()
    pdf.set_font("Helvetica", "B", 14)
    pdf.cell(0, 10, safe("Top 10 recommandations"), new_x="LMARGIN", new_y="NEXT")
    pdf.ln(2)
    pdf.set_font("Helvetica", "", 9)
    top10 = [
        "1. Base de connaissances interne Defrilex (SOPs, rubriques QA, playbooks)",
        "2. NCIHC + CHIA + NAJIT (standards interpretes)",
        "3. O*NET Web Services API (competences par role)",
        "4. UMLS / NLM (terminologie medicale)",
        "5. ICMI (formation centre de contact)",
        "6. CXPA Book of Knowledge / CCXP (experience client)",
        "7. ATD Talent Development Capability Model (L&D)",
        "8. Skillsoft Percipio ou LinkedIn Learning (bibliotheque licenciee)",
        "9. OER Commons (CC-BY, creation de cours)",
        "10. Gong + donnees QA internes (coaching ventes, si licencie)",
    ]
    for item in top10:
        pdf.cell(0, 7, safe(item), new_x="LMARGIN", new_y="NEXT")

    pdf.ln(6)
    pdf.set_font("Helvetica", "B", 11)
    pdf.cell(0, 8, safe("Phases de deploiement"), new_x="LMARGIN", new_y="NEXT")
    pdf.set_font("Helvetica", "", 9)
    phases = [
        "Phase 1 (MVP) : Interne + standards gratuits + O*NET + OER Commons",
        "Phase 2 : Integration QA, revues de performance, workflows d'approbation",
        "Phase 3 : Contenus licencies (Percipio, LinkedIn, ICMI, Sandler, Gong)",
        "Phase 4 : Coaching adaptatif et creation de cours a l'echelle de l'entreprise",
    ]
    for p in phases:
        pdf.cell(0, 7, safe(p), new_x="LMARGIN", new_y="NEXT")

    pdf.output(OUTPUT)
    print(f"PDF created: {OUTPUT}")


if __name__ == "__main__":
    main()
