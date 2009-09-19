-- Updates diplomas
UPDATE profile_education_degree_enum SET abbreviation = 'PhD', degree = 'Doctorat', level = 8 WHERE degree = 'PhD';
UPDATE profile_education_degree_enum SET abbreviation = 'Lic.', level = 3 WHERE degree = 'Licence';
UPDATE profile_education_degree_enum SET abbreviation = 'MSc', degree = 'Master of Science', level = 5 WHERE degree = 'MS';
UPDATE profile_education_degree_enum SET abbreviation = 'DEA', degree = "Diplôme d'Études Approfondies", level = 5 WHERE degree = 'DEA';
UPDATE profile_education_degree_enum SET abbreviation = 'ME', degree = 'Master of Economics', level = 5 WHERE degree = 'ME';
UPDATE profile_education_degree_enum SET abbreviation = 'MBA', degree = 'Master of Business Administration', level = 5 WHERE degree = 'MBA';
UPDATE profile_education_degree_enum SET abbreviation = 'MiF', degree = 'Master in Finance', level = 5 WHERE degree = 'MiF';
UPDATE profile_education_degree_enum SET abbreviation = 'MPA', degree = 'Master of Public Administration', level = 5 WHERE degree = 'MPA';
UPDATE profile_education_degree_enum SET abbreviation = 'MIA', degree = 'Master of International Affairs', level = 5 WHERE degree = 'MIA';
UPDATE profile_education_degree_enum SET abbreviation = 'Corps', degree = 'Corps', level = 5 WHERE degree = 'Corps';
UPDATE profile_education_degree_enum SET abbreviation = 'Ing.', degree = 'Ingénieur', level = 5 WHERE degree = 'Ingénieur';
UPDATE profile_education_degree_enum SET abbreviation = 'Dipl.', degree = 'Diplôme', level = 0 WHERE degree = 'Diplôme';

INSERT INTO  profile_education_degree_enum (abbreviation, degree, level)
     VALUES  ('Agr.', 'Agrégation', 4), ('CAPES', 'Certificat d\'Aptitude au Professorat de l\'Enseignement du Second degré', 4),
             ('DESS', 'Diplôme d\'Études Supérieures Spécialisées', 5), ('BTS', 'Brevet de Technicien Supérieur', 2), ('MA', 'Master of Arts', 5),
             ('Maîtr.', 'Maîtrise', 4), ('HDR', 'Habilitation à Diriger des Recherches', 8), ('DEUG', 'Diplôme d\'Études Universitaires Générales', 2),
             ('MEE', 'Master of Electrical Engineering', 5), ('MPhil', 'Master of Philosophy', 5), ('MUP', 'Master of Urban Planning', 5),
             ('MME', 'Master of Mechanical Engineering', 5), ('MCP', 'Master of City Planning', 5), ('BA', 'Bachelor of Arts', 3),
             ('MEl', 'Master of Electronics', 5), ('MM', 'Master of Management', 5), ('MIB', 'Master of International Business', 5),
             ('MC', 'Master of Chemistry', 5), ('MEM', 'Master of Engineering in Manufacturing', 5), ('MEng', 'Master of Engineering', 5),
             ('MCE', 'Master of Chemical Engineering', 5), ('M', 'Master', 5), ('MMS', 'Master of Military Studies', 5),
             ('MSI', 'Master of Science in Information', 5),
             ('DESCF', 'Diplôme d\'Études Supérieures Comptables et Financières', 5), ('MB', 'Master of Biotechnology', 5);

-- Updates universities
UPDATE profile_education_enum
SET abbreviation = 'Télécom', name = 'Télécom ParisTech', url = 'http://www.telecom-paristech.fr/'
WHERE name = 'Télécom';
UPDATE profile_education_enum
SET abbreviation = 'ENGREF', name = 'École Nationale du Génie Rural des Eaux et des Forêts', url = 'http://www.agroparistech.fr/-Ecole-interne-ENGREF-.html'
WHERE name = 'ENGREF';
UPDATE profile_education_enum
SET abbreviation = 'INSEE', name = 'Institut National de la Statistique et des Études Économiques'
WHERE name = 'INSEE';
UPDATE profile_education_enum
SET abbreviation = 'Météo', name = 'École Nationale de la Météorologie'
WHERE name = 'Météo';
UPDATE profile_education_enum
SET abbreviation = 'Mines', name = 'Mines ParisTech'
WHERE name = 'Mines';
UPDATE profile_education_enum
SET abbreviation = 'Ponts', name = 'École des Ponts ParisTech'
WHERE name = 'Ponts';
UPDATE profile_education_enum
SET abbreviation = 'ENSAE', name = 'École Nationale de la Statistique et de l\'Administration Économique ParisTech'
WHERE name = 'ENSAE';
UPDATE profile_education_enum
SET abbreviation = 'ENSAM', name = 'Arts et Métiers ParisTech'
WHERE name = 'ENSAM';
UPDATE profile_education_enum
SET abbreviation = 'ENSEEIHT', name = 'École Nationale Supérieure d\'Électrotechnique, d\'Électronique, d\'Informatique, d\'Hydraulique et des Télécommunications'
WHERE name = 'ENSEEIHT';
UPDATE profile_education_enum
SET abbreviation = 'ENSIMAG', name = 'École Nationale Supérieure d\'Informatique et de Mathématiques Appliquées de Grenoble', url = 'http://ensimag.grenoble-inp.fr/'
WHERE name = 'ENSIMAG';
UPDATE profile_education_enum
SET abbreviation = 'ENSTA', name = 'École Nationale Supérieure de Techniques Avancées'
WHERE name = 'ENSTA';
UPDATE profile_education_enum
SET abbreviation = 'ENSPM', name = 'École Nationale Supérieure du Pétrole et des Moteurs'
WHERE name = 'ENSPM';
UPDATE profile_education_enum
SET abbreviation = 'INAPG', name = 'Institut National Agronomique Paris-Grignon', url = 'http://www.agroparistech.fr/'
WHERE name = 'INAPG';
UPDATE profile_education_enum
SET abbreviation = 'HEC', name = 'École des Hautes Études Commerciales'
WHERE name = 'HEC';
UPDATE profile_education_enum
SET abbreviation = 'X', name = 'École polytechnique', url = 'http://www.polytechnique.edu/'
WHERE name = 'X';
UPDATE profile_education_enum
SET abbreviation = 'SUPAERO', name = 'École Nationale Supérieure de l\'Aéronautique et de l\'Espace', url = 'http://www.isae.fr/'
WHERE name = 'Supaéro';
UPDATE profile_education_enum
SET abbreviation = 'SupOptique', name = 'Institut d\'Optique Théorique et Appliquée'
WHERE name = 'Supoptique';
UPDATE profile_education_enum
SET abbreviation = 'Supélec', name = 'École Supérieure d\'Électricité'
WHERE name = 'Supélec';
UPDATE profile_education_enum
SET abbreviation = 'ENA', name = 'École Nationale d\'Administration'
WHERE name = 'ENA';
UPDATE profile_education_enum
SET abbreviation = 'INSEAD', name = 'Institut Européen d\'Administration des Affaires', url = 'http://www.insead.edu/'
WHERE name = 'INSEAD';
UPDATE profile_education_enum
SET abbreviation = 'Chimie Paris', name = 'Chimie Paris ParisTech', url = 'http://www.enscp.fr/'
WHERE name = 'Chimie Paris';
UPDATE profile_education_enum
SET abbreviation = 'INSTN', name = 'Institut National des Sciences et Techniques Nucléaires'
WHERE name = 'INSTN';
UPDATE profile_education_enum
SET abbreviation = 'UMPC', name = 'Université Pierre-et-Marie-Curie (Paris-VI)', url = 'http://www.upmc.fr/'
WHERE name = 'Univ Paris  6 (Pierre et Marie Curie - Jussieu)';
UPDATE profile_education_enum
SET abbreviation = 'Paris-Sud', name = 'Université Paris-Sud (Paris-XI)'
WHERE name = 'Univ Paris 11 (Orsay)';
UPDATE profile_education_enum
SET abbreviation = 'Paris-Diderot', name = 'Université Denis Diderot (Paris-VII)', url = 'http://www.univ-paris7.fr/'
WHERE name = 'Univ Paris  7 (Denis Diderot - Jussieu)';
UPDATE profile_education_enum
SET abbreviation = 'Dauphine', name = 'Université de Technologie en Sciences des Organisations et de la Décision de Paris-Dauphine (Paris-IX)'
WHERE name = 'Univ Paris  9 (Dauphine)';
UPDATE profile_education_enum
SET abbreviation = 'Panthéon-Sorbonne', name = 'Université Panthéon-Sorbonne (Paris-I)'
WHERE name = 'Univ Paris  1 (Panthéon-Sorbonne)';
UPDATE profile_education_enum
SET abbreviation = 'Nanterre', name = 'Université de Paris Ouest - Nanterre La Défense (Paris-X)'
WHERE name = 'Univ Paris 10 (Nanterre)';
UPDATE profile_education_enum
SET abbreviation = 'Paris-Descartes', name = 'Université Paris Descartes (Paris-V)'
WHERE name = 'Univ Paris  5 (René Descartes)';
UPDATE profile_education_enum
SET abbreviation = 'Sorbonne Nouvelle', name = 'Université Sorbonne Nouvelle (Paris-III)'
WHERE name = 'Univ Paris  3 (Sorbonne Nouvelle)';
UPDATE profile_education_enum
SET abbreviation = 'Vincennes-Saint-Denis', name = 'Université de Vincennes à Saint-Denis (Paris-VIII)'
WHERE name = 'Univ Paris  8 (Vincennes - Saint Denis)';
UPDATE profile_education_enum
SET abbreviation = 'Paris-Val de Marne', name = 'Université Paris-Val de Marne (Paris-XII)'
WHERE name = 'Univ Paris 12 (Val de Marne)';
UPDATE profile_education_enum
SET abbreviation = 'Paris-Nord', name = 'Université Paris-Nord (Paris-XIII)'
WHERE name = 'Univ Paris 13 (Nord)';
UPDATE profile_education_enum
SET abbreviation = 'Paris-Sorbonne', name = 'Université Paris-Sorbonne (Paris-IV)'
WHERE name = 'Univ Paris  4 (Sorbonne)';
UPDATE profile_education_enum
SET abbreviation = 'Assas', name = 'Université Panthéon-Assas (Paris-II)'
WHERE name = 'Univ Paris  2 (Panthéon - Assas)';
UPDATE profile_education_enum
SET abbreviation = 'CDI', name = 'Collège des Ingénieurs'
WHERE name = 'Collège des Ingénieurs';
UPDATE profile_education_enum
SET abbreviation = 'ENS Ulm', name = 'École Normale Supérieure'
WHERE name = 'ENS Ulm';
UPDATE profile_education_enum
SET abbreviation = 'ENS Lyon', name = 'École Normale Supérieure de Lyon '
WHERE name = 'ENS Lyon';
UPDATE profile_education_enum
SET abbreviation = 'ENS Cachan', name = 'École Normale Supérieure de Cachan'
WHERE name = 'ENS Cachan';
UPDATE profile_education_enum
SET abbreviation = 'ESPCI', name = 'ESPCI ParisTech'
WHERE name = 'ESPCI';
UPDATE profile_education_enum
SET abbreviation = 'Sciences Po', name = 'Institut d\'Études Politiques de Paris'
WHERE name = 'IEP Paris';
UPDATE profile_education_enum
SET abbreviation = 'EHESS', name = 'École des Hautes Études en Sciences Sociales'
WHERE name = 'EHESS';
UPDATE profile_education_enum
SET abbreviation = 'ENSIC', name = 'École Nationale Supérieure des Industries Chimiques'
WHERE name = 'ENSIC';
UPDATE profile_education_enum
SET abbreviation = 'Grenoble INP', name = 'Institut Polytechnique de Grenoble', url = 'http://www.grenoble-inp.fr/'
WHERE name = 'INPG';
UPDATE profile_education_enum
SET abbreviation = 'ESSEC', name = 'École Supérieure des Sciences Économiques et Commerciales'
WHERE name = 'ESSEC';
UPDATE profile_education_enum
SET abbreviation = 'INPL', name = 'Institut National Polytechnique de Lorraine'
WHERE name = 'INPL';
UPDATE profile_education_enum
SET abbreviation = 'ENSAPB', name = 'École Nationale Supérieure d\'Architecture de Paris-Belleville'
WHERE name = 'EAPB (Ecole Architecture Paris Belleville)';
UPDATE profile_education_enum
SET abbreviation = 'ENSAPLV', name = 'École Nationale Supérieure d\'Architecture de Paris-La Villette'
WHERE name = 'EAPLV (Ecole Architecture Paris La Villette)';
UPDATE profile_education_enum
SET abbreviation = 'EAVT', name = 'École d\'Architecture de la Ville et des Territoires à Marne-la-Vallée'
WHERE name = 'EAVT (Ecole d\'architecture de Marne La Vallee)';
UPDATE profile_education_enum
SET abbreviation = 'ENSP', name = 'École Nationale Supérieure du Paysage'
WHERE name = 'ENSP Versailles';
UPDATE profile_education_enum
SET abbreviation = '', name = 'École Nationale Supérieure du Génie Maritime', url = 'http://www.ensta.fr/'
WHERE name = 'Génie maritime (Ecole nationale supérieur du)';
UPDATE profile_education_enum
SET abbreviation = 'CPA de Paris', name = 'Centre de Perfectionnement aux Affaires de Paris', url = ''
WHERE name = 'Centre de Perfectionnement aux Affaires';
UPDATE profile_education_enum
SET abbreviation = '', name = 'ESCP-EAP', url = 'http://www.escp-eap.eu/'
WHERE name = 'ESCP-EAP';
UPDATE profile_education_enum
SET abbreviation = 'CEPE', name = 'Centre d\'Études des Programmes Économiques'
WHERE name = 'CEPE';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Institut des Actuaires', url = 'http://www.institutdesactuaires.com/'
WHERE name = 'Institut des actuaires';
UPDATE profile_education_enum
SET abbreviation = 'CEIPI', name = 'Centre d\'Études Internationales de la Propriété Industrielle'
WHERE name = 'CEIPI';
UPDATE profile_education_enum
SET abbreviation = 'Université Paul-Sabatier', name = 'Université Paul-Sabatier (Toulouse III)'
WHERE name = 'Univ Toulouse III (Paul Sabatier)';
UPDATE profile_education_enum
SET abbreviation = 'Université de Provence', name = 'Université de Provence (Aix-Marseille I)'
WHERE name = 'Université de Provence';
UPDATE profile_education_enum
SET abbreviation = 'INSA Rouen', name = 'Institut National des Sciences Appliquées de Rouen'
WHERE name = 'INSA Rouen';
UPDATE profile_education_enum
SET abbreviation = 'IAE de Paris', name = 'Institut d\'Administration des Entreprises de Paris', url = 'http://iae.univ-paris1.fr/'
WHERE name = 'Institut d\'Administration des Entreprises';

UPDATE profile_education_enum
SET abbreviation = 'Berkeley', name = 'University of California, Berkeley', country = 'US'
WHERE name = 'Univ Berkeley';
UPDATE profile_education_enum
SET abbreviation = 'CalTech', name = 'California Institute of Technology', country = 'US'
WHERE name = 'CalTech';
UPDATE profile_education_enum
SET abbreviation = 'MIT', name = 'Massachusetts Institute of Technology', country = 'US'
WHERE name = 'Massachusetts Institute of Technology';
UPDATE profile_education_enum
SET abbreviation = 'Cornell', name = 'Cornell University', country = 'US'
WHERE name = 'Univ Cornell';
UPDATE profile_education_enum
SET abbreviation = 'Stanford', name = 'Stanford University', country = 'US'
WHERE name = 'Univ Stanford';
UPDATE profile_education_enum
SET abbreviation = 'UCLA', name = 'University of California, Los Angeles', country = 'US'
WHERE name = 'Univ UCLA';
UPDATE profile_education_enum
SET abbreviation = '', name = 'University of Illinois', country = 'US'
WHERE name = 'Univ Illinois';
UPDATE profile_education_enum
SET abbreviation = 'Michigan', name = 'University of Michigan, Ann Arbor', country = 'US'
WHERE name = 'Univ Michigan';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Seattle University', country = 'US'
WHERE name = 'Univ Seattle';
UPDATE profile_education_enum
SET abbreviation = 'UGA', name = 'University of Georgia', country = 'US'
WHERE name = 'Univ Georgia';
UPDATE profile_education_enum
SET abbreviation = 'UT Austin', name = 'University of Texas at Austin', country = 'US'
WHERE name = 'Univ Texas';
UPDATE profile_education_enum
SET abbreviation = 'RIP', name = 'Rensselaer Polytechnic Institute', country = 'US'
WHERE name = 'Univ Rensselaer';
UPDATE profile_education_enum
SET abbreviation = 'NYU', name = 'New York University', country = 'US'
WHERE name = 'Univ New York';
UPDATE profile_education_enum
SET abbreviation = 'Harvard', name = 'Harvard University', country = 'US'
WHERE name = 'Univ Harvard';
UPDATE profile_education_enum
SET abbreviation = 'The Wharton School', name = 'The Wharton School of the University of Pennsylvania', country = 'US', url = 'http://www.wharton.upenn.edu/'
WHERE name = 'Univ Wharton';
UPDATE profile_education_enum
SET abbreviation = 'Columbia University', name = 'Columbia University in the City of New York', country = 'US'
WHERE name = 'Univ Columbia';
UPDATE profile_education_enum
SET abbreviation = 'WSBS', name = 'Watson School of Biological Sciences', country = 'US'
WHERE name = 'Watson School of Biological Sciences';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Colorado School of Mines', country = 'US'
WHERE name = 'Univ Colorado School of Mines';
UPDATE profile_education_enum
SET abbreviation = 'Princeton', name = 'Princeton University', country = 'US'
WHERE name = 'Univ Princeton';
UPDATE profile_education_enum
SET abbreviation = 'Georgia Tech', name = 'Georgia Institute of Technology', country = 'US'
WHERE name = 'GeorgiaTech';
UPDATE profile_education_enum
SET abbreviation = 'JHU', name = 'Johns Hopkins University', country = 'US'
WHERE name = 'Univ Johns Hopkins';
UPDATE profile_education_enum
SET abbreviation = '', name = 'University of Chicago', country = 'US'
WHERE name = 'Univ Chicago';
UPDATE profile_education_enum
SET abbreviation = 'Yale', name = 'Yale University', country = 'US'
WHERE name = 'Univ Yale';
UPDATE profile_education_enum
SET abbreviation = 'TAMU', name = 'Texas A&M University', country = 'US'
WHERE name = 'Texas A&M University';
UPDATE profile_education_enum
SET abbreviation = 'UCSB', name = 'University of California, Santa Barbara', country = 'US'
WHERE name = 'Univ Santa-Barbara';
UPDATE profile_education_enum
SET abbreviation = 'Kellogg', name = 'Kellogg School of Management', country = 'US'
WHERE name = 'Kellogg School of Management';
UPDATE profile_education_enum
SET abbreviation = '', name = 'University of Iowa', country = 'US'
WHERE name = 'Univ Iowa';
UPDATE profile_education_enum
SET abbreviation = 'UW-Madison', name = 'University of Wisconsin-Madison', country = 'US', url = 'http://www.wisc.edu/'
WHERE name = 'Univ Wisconsin-Madison';
UPDATE profile_education_enum
SET abbreviation = 'UCSD', name = 'University of California, San Diego', country = 'US'
WHERE name = 'Univ San Diego';
UPDATE profile_education_enum
SET abbreviation = 'NU', name = 'Northwestern University', country = 'US'
WHERE name = 'Univ Northwestern';
UPDATE profile_education_enum
SET abbreviation = 'CU', name = 'University of Colorado at Boulder', country = 'US'
WHERE name = 'Univ Colorado at Boulder';
UPDATE profile_education_enum
SET abbreviation = 'CMU', name = 'Carnegie Mellon University', country = 'US'
WHERE name = 'Univ Carnegie Mellon';
UPDATE profile_education_enum
SET abbreviation = 'Carolina', name = 'University of North Carolina at Chapel Hill', country = 'US'
WHERE name = 'Univ of North Carolina at Chapel Hill';
UPDATE profile_education_enum
SET abbreviation = 'UM', country = 'US'
WHERE name = 'University of Miami';

UPDATE profile_education_enum
SET abbreviation = 'TU Berlin', name = 'Technische Universität Berlin', country = 'DE'
WHERE name = 'Univ TU Berlin';
UPDATE profile_education_enum
SET abbreviation = 'TU Darmstadt', name = 'Technische Universität Darmstadt', country = 'DE'
WHERE name = 'Univ TU Darmstadt';
UPDATE profile_education_enum
SET abbreviation = 'TU München', name = 'Technische Universität München', country = 'DE'
WHERE name = 'Univ TU München';
UPDATE profile_education_enum
SET abbreviation = 'Universität Karlsruhe', name = 'Universität Karlsruhe (TH)', country = 'DE'
WHERE name = 'Univ Karlsruhe';
UPDATE profile_education_enum
SET abbreviation = 'RWTH', name = 'RWTH Aachen University', country = 'DE'
WHERE name = 'Univ RWTH-Aachen';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Universität Stuttgart', country = 'DE'
WHERE name = 'Univ Stuttgart';

UPDATE profile_education_enum
SET abbreviation = 'EPM', name = 'École Polytechnique de Montréal', country = 'CA'
WHERE name = 'EP Montréal';
UPDATE profile_education_enum
SET abbreviation = 'UBC', name = 'University of British Columbia', country = 'CA'
WHERE name = 'Univ British Columbia';
UPDATE profile_education_enum
SET abbreviation = 'McGill', name = 'McGill University', country = 'CA'
WHERE name = 'Univ McGill';
UPDATE profile_education_enum
SET abbreviation = 'UQÀM', name = 'Université du Québec à Montréal', country = 'CA' WHERE id = 125;

UPDATE profile_education_enum
SET abbreviation = 'UPC', name = 'Universitat Politècnica de Catalunya', country = 'ES'
WHERE name = 'Univ Catalunya';
UPDATE profile_education_enum
SET abbreviation = 'UPM', name = 'Universidad Politècnica de Madrid', country = 'ES'
WHERE name = 'Univ Madrid';
UPDATE profile_education_enum
SET abbreviation = 'UPF', name = 'Universitat Pompeu Fabra', country = 'ES'
WHERE name = 'Univ Pompeu Fabra';

UPDATE profile_education_enum
SET abbreviation = 'Chalmers', name = 'Chalmers Tekniska Högskola', country = 'SE'
WHERE name = 'Univ Chalmers';
UPDATE profile_education_enum
SET abbreviation = 'KTH', name = 'Kungliga Tekniska Högskolan', country = 'SE'
WHERE name = 'Univ KTH';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Stockholms Universitet', country = 'SE'
WHERE name = 'Univ Stockholm';

UPDATE profile_education_enum
SET abbreviation = 'TU Delft', name = 'Technische Universiteit Delft', country = 'NL'
WHERE name = 'Univ TU Delft';
UPDATE profile_education_enum
SET abbreviation = 'RSM', name = 'Rotterdam School of Management, Erasmus University', country = 'NL'
WHERE name = 'RSM';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Universiteit Leiden', country = 'NL'
WHERE name = 'Univ Leiden';

UPDATE profile_education_enum
SET abbreviation = '', name = 'Kyoto University', country = 'JP'
WHERE name = 'Univ Kyoto';
UPDATE profile_education_enum
SET abbreviation = 'Todai', name = 'University of Tokyo', country = 'JP'
WHERE name = 'Univ Tokyo';
UPDATE profile_education_enum
SET abbreviation = 'Tokyo Tech', name = 'Tokyo Institute of Technology', country = 'JP'
WHERE name = 'Tokyo Institute of Technology';

UPDATE profile_education_enum
SET abbreviation = 'UNIL', name = 'Université de Lausanne', country = 'CH'
WHERE name = 'Univ Lausanne';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Universität Zürich', country = 'CH'
WHERE name = 'Univ Zürich';
UPDATE profile_education_enum
SET abbreviation = 'EPFL', name = 'École Polytechnique Fédérale de Lausanne', country = 'CH'
WHERE name = 'EP Fédérale Lausanne';
UPDATE profile_education_enum
SET abbreviation = 'IMD', name = 'International Institute for Management Development', country = 'CH'
WHERE name = 'Institute for Management Development';
UPDATE profile_education_enum
SET abbreviation = 'ETH Zürich', name = 'Eidgenössische Technische Hochschule Zürich', country = 'CH'
WHERE name = 'ETH Zürich';

UPDATE profile_education_enum
SET abbreviation = 'UNIMI', name = 'Università degli Studi di Milano', country = 'IT'
WHERE name = 'Univ Milano';
UPDATE profile_education_enum
SET abbreviation = 'UNITO', name = 'Università degli Studi di Torino', country = 'IT'
WHERE name = 'Univ Torino';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Politecnico di Milano', country = 'IT'
WHERE name = 'Politecnico di Milano';
UPDATE profile_education_enum
SET abbreviation = 'EUI', name = 'European University Institute', country = 'IT'
WHERE name = 'Institut Universitaire Européen';
UPDATE profile_education_enum
SET abbreviation = 'Università Bocconi', name = 'Università Commerciale Luigi Bocconi', url = 'http://www.unibocconi.it/', country = 'IT'
WHERE name = 'Université Bocconi';

UPDATE profile_education_enum
SET abbreviation = 'MGU', name = 'Lomonosov Moscow State University', country = 'RU'
WHERE name = 'Univ Moscow (lomonosov)';
UPDATE profile_education_enum
SET abbreviation = 'Bauman MSTU', name = 'Bauman Moscow State Technical University', country = 'RU', url = 'http://www.bmstu.ru/'
WHERE name = 'Univ Moscow (Bauman)';

UPDATE profile_education_enum
SET abbreviation = 'Technion', name = 'Israel Institute of Technology', country = 'IL'
WHERE name = 'Univ Technion';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Weizmann Institute of Science', country = 'IL'
WHERE name = 'Institut Weizmann';

UPDATE profile_education_enum
SET abbreviation = 'Oxford', name = 'University of Oxford', country = 'GB'
WHERE name = 'Univ Oxford';
UPDATE profile_education_enum
SET abbreviation = '', name = 'London Business School', country = 'GB'
WHERE name = 'London Business School';
UPDATE profile_education_enum
SET abbreviation = 'LSE', name = 'The London School of Economics and Political Science', country = 'GB'
WHERE name = 'London School of Economics';
UPDATE profile_education_enum
SET abbreviation = 'Cambridge', name = 'University of Cambridge', country = 'GB'
WHERE name = 'Univ Cambridge';
UPDATE profile_education_enum
SET abbreviation = 'Imperial College', name = 'Imperial College London', country = 'GB', url = 'http://www3.imperial.ac.uk/'
WHERE name = 'Imperial College';
UPDATE profile_education_enum
SET abbreviation = 'Henley', name = 'Henley Management College', country = 'GB', url = 'http://www.henley.reading.ac.uk/'
WHERE name = 'Henley Management College';
UPDATE profile_education_enum
SET abbreviation = '', name = 'University of Southampton', country = 'GB', url = 'http://www.soton.ac.uk/'
WHERE name = 'Univ Southampton';
UPDATE profile_education_enum
SET abbreviation = '', name = 'Cardiff University', country = 'GB'
WHERE name = 'Univ Cardiff';

UPDATE profile_education_enum
SET abbreviation = 'UNSW', name = 'The University of New South Wales', country = 'AU'
WHERE name = 'Univ New South Wales  (Sydney Australia)';

UPDATE profile_education_enum
SET abbreviation = 'THU', name = 'Tsinghua University', country = 'CN'
WHERE name = 'Univ Tsinghua';

UPDATE profile_education_enum
SET abbreviation = 'NUS', name = 'National University of Singapore', country = 'SG'
WHERE name = 'National University of Singapore';

UPDATE profile_education_enum
SET abbreviation = 'NTNU', name = 'Norwegian University of Science and Technology', country = 'NO', url = 'http://www.ntnu.no/'
WHERE name = 'Univ Trondheim';

UPDATE profile_education_enum
SET abbreviation = '', country = 'GB'
WHERE name = 'University of Surrey';
UPDATE profile_education_enum
SET abbreviation = '', country = 'GB'
WHERE name = 'Heriot-Watt University';
UPDATE profile_education_enum
SET abbreviation = 'UCL', country = 'GB'
WHERE name = 'University College London';

UPDATE profile_education_enum
SET abbreviation = 'OIV', country = 'FR'
WHERE name = 'Organisme International de la Vigne et du Vin';
UPDATE profile_education_enum
SET abbreviation = 'NSU', name = 'Novosibirsk State University', url = 'http://www.nsu.ru/', country = 'RU'
WHERE name = 'Université de Novossibirsk (Новосибирский Государственный Университет)';

UPDATE profile_education_enum
SET abbreviation = 'IITs', country = 'IN'
WHERE name = 'Indian Institutes of Technology';

UPDATE profile_education_enum
SET name = 'Institut des Hautes Études de Défense Nationale', country = 'FR', url ='http://www.ihedn.fr/',
abbreviation = 'IHEDN'
WHERE name = 'IHEDN';

UPDATE profile_education_enum
SET name = "Centre des Hautes Études de l'Armement", country = 'FR', url ='http://www.chear.defense.gouv.fr/',
WHERE name = 'CHEAr'
WHERE name = 'CHEAr';

-- Cleans duplicated entries
UPDATE profile_education SET eduid = 0 WHERE eduid = 70;
DELETE FROM profile_education_enum WHERE id = 70;
DELETE FROM profile_education_degree WHERE eduid = 70;

UPDATE profile_education SET eduid = 91 WHERE eduid = 106;
DELETE FROM profile_education_enum WHERE id = 106;
DELETE FROM profile_education_degree WHERE eduid = 106;

-- Adds new universities needed for the AX directory
INSERT INTO  profile_education_enum (name, url, country, abbreviation)
     VALUES  ('Institut Supérieur de l\'Aéronautique et de l\'Espace', 'http://www.isae.fr/', 'FR', 'ISAE'),
             ('École du Personnel Navigant d\'Essais et de Réception',
              'http://www.defense.gouv.fr/dga/archives/l_epner_ecole_du_personnel_navigant_d_essais_et_de_reception', 'FR', 'EPNER'),
             ('Agrocampus Ouest', 'http://www.agrocampus-ouest.fr/', 'FR', 'ENSAR'),
             ('Montpellier SupAgro', 'http://www.supagro.fr/', 'fr', ''),
             ('Institut Supérieur des Matériaux et de la Construction Mécanique Saint-Ouen', 'http://www.cefi.org/BOUCHON/BS/ISMCM_Saint-Ouen.htm',
              'FR', 'ISMCM Saint-Ouen'),
             ('École Centrale d\'Électronique ', 'http://www.ece.fr/', 'FR', 'ECE'),
             ('École Nationale de l\'Aviation Civile', 'http://www.enac.fr/', 'FR', 'ENAC'),
             ('Centre des Hautes Études de la Construction', 'http://www.chec.fr/', 'FR', 'CHEC'),
             ('École de l\'Air', 'http://www.ecole-air.air.defense.gouv.fr/index.php?option=com_content&task=view&id=203&Itemid=251', 'FR', ''),
             ('Institut Supérieur des Affaires', '', 'FR', 'ISA'),
             ('École Supérieure de Gestion de Paris', 'http://www.esg.fr/', 'FR', 'ESG Paris'),
             ('Institut des Hautes Études Européennes', 'http://www-ihee.u-strasbg.fr/', 'FR', 'IHEE'),
             ('École Nationale de la Magistrature', 'http://www.enm.justice.fr/', 'FR', 'ENM'),
             ('Institut de Formation Supérieure BioMédicale', 'http://www.igr.fr/ifsbm/', 'FR', 'IFSBM'),
             ('Institut Supérieur de l\'AgroAlimentaire', 'http://www.isaa.fr/', 'FR', 'ISAA'),
             ('École des Mines d\'Alès', 'http://www.ema.fr/', 'FR', 'EMA'),
             ('Syracuse University', 'http://www.syr.edu/', 'US', 'SU'),
             ('Dartmouth College', 'http://www.dartmouth.edu/', 'US', ''),
             ('International Teachers Programme', 'http://www.itp-schools.org/', '', 'ITP'),
             ('University of Kentucky', 'http://www.uky.edu/', 'US', 'UK'),
             ('Marine Corps University', 'http://www.mcu.usmc.mil/', 'US', 'MCU'),
             ('Chartered Institute of Management Accountants', 'http://www.cimaglobal.com/', 'GB', 'CIMA'),
             ('Chartered Financial Analyst Institute', 'http://www.cfainstitute.org/', 'US', 'CFA Institute'),
             ('Naval Postgraduate School', 'http://www.nps.edu/', 'US', 'NPS'),
             ('Royal College of Art', 'http://www.rca.ac.uk/', 'GB', 'RCA'),
             ('Uniwersytet Gdański', 'http://www.univ.gda.pl/', 'PL', ''),
             ('College of Europe', 'http://www.coleurope.eu/', '', ''),
             ('Purdue University', 'http://www.purdue.edu/', 'US', ''),
             ('Queen\'s University', 'http://www.queensu.ca/', 'CA', 'Queen\'s'),
             ('Université de Bretagne Occidentale', 'http://www.univ-brest.fr/', 'FR', 'UBO'),
             ('University of California, Davis', 'http://www.ucdavis.edu/', 'US', 'UC Davis'),
             ('Universität Stuttgarti', 'http://www.uni-stuttgart.de/', 'DE', ''),
             ('Universitatea Politehnica din Bucureşti', 'http://www.pub.ro/', 'RO', 'Politehnica din Bucureşti'),
             ('University of Birmingham', 'http://www.bham.ac.uk/', 'GB', ''),
             ('University of Pennsylvania', 'http://www.upenn.edu/', 'US', ''),
             ('University of Rome', '', 'IT', ''),
             ('University of Sheffield', 'http://www.shef.ac.uk/', 'GB', ''),
             ('University of Utah', 'http://www.utah.edu/', 'US', ''),
             ('University of Washington', 'http://www.washington.edu/', 'US', ''),
             ('Urbana University', 'http://www.urbana.edu/', 'US', ''),
             ('Université de Technologie de Compiègne', 'http://www.utc.fr/', 'FR', 'UTC'),
             ('Virginia Polytechnic Institute and State University', 'http://www.vt.edu/', 'US', 'Virginia Tech'),
             ('Université Claude Bernard (Lyon I)', 'http://www.univ-lyon1.fr/', 'FR', 'Université Claude Bernard'),
             ('Cleveland State University', 'http://www.csuohio.edu/', 'US', 'CSU'),
             ('Centre de Perfectionnement aux Affaires du Nord', '', 'FR', 'CPA du Nord'),
             ('Centre de Perfectionnement aux Affaires de Lyon', '', '', 'CPA de Lyon'),
             ('Amherst College', 'http://www.amherst.edu/', 'US', ''),
             ('Ottawa University', 'http://www.ottawa.edu/', 'US', 'OU'),
             ('Indiana University', 'http://www.indiana.edu/', 'US', ''),
             ('University of Notre Dame du Lac', 'http://www.nd.edu/', 'US', 'Notre Dame'),
             ('University of Maryland, College Park ', 'http://www.umd.edu/', 'US', 'UMCP'),
             ('Vanderbilt University', 'http://www.vanderbilt.edu/', 'US', ''),
             ('Institut National Polytechnique de Toulouse', 'http://www.inp-toulouse.fr/', 'FR', 'INP Toulouse'),
             ('University of Houston', 'http://www.uh.edu/', 'US', 'UH'),
             ('École Spéciale des Travaux Publics, du Bâtiment et de l\'Industrie', 'http://www.estp.fr/', 'FR', 'ESTP'),
             ('Université Jean-Moulin (Lyon-III)', 'http://www.univ-lyon3.fr/', 'FR', 'Université Jean-Moulin'),
             ('École Nationale Supérieure d\'Ingénieurs Électriciens de Grenoble', 'http://ense3.grenoble-inp.fr/', 'FR', 'ENSIEG'),
             ('École Nationale Supérieure d\'Hydraulique et de Mécanique de Grenoble', 'http://ense3.grenoble-inp.fr/', 'FR', 'ENSHMG'),
             ('Université de Rennes 1', 'http://www.univ-rennes1.fr/', 'FR', ''),
             ('École Centrale Paris', 'http://www.ecp.fr/', 'FR', 'Centrale Paris'),
             ('University of Dallas', 'http://www.udallas.edu/', 'US', ''),
             ('Pontifical Catholic University of Chile', 'http://www.uc.cl/', 'CL', 'PUC'),
             ('Universitat Oberta de Catalunya', 'http://www.uoc.edu/web/eng/', 'ES', 'UOC'),
             ('University of California, Irvine', 'http://www.uci.edu/', 'US', 'UCI'),
             ('Association Francophone de Management de Projet', 'http://www.afitep.fr/', 'FR', 'AFITEP'),
             ('Centre Européen d\'Éducation Permanente', 'http://www.cedep.fr/', 'FR', 'CEDEP'),
             ('Collège Interarmées de Défense', 'http://www.college.interarmees.defense.gouv.fr/', 'FR', ''),
             ('Centre de Formation des Journalistes', 'http://www.cfpj.com/', 'FR', 'CFJ'),
             ('Institut National des Hautes Études de Sécurité', 'http://www.inhes.interieur.gouv.fr/', 'FR', 'INHES'),
             ('Université d\'Orléans', 'http://www.univ-orleans.fr/', 'FR', '');


-- Médecine is not a university but an educational field
REPLACE INTO  profile_education (uid, id, fieldid, eduid, degreeid)
      SELECT  e.uid, e.id, f.id, 0, d.id
        FROM  profile_education             AS e
  INNER JOIN  profile_education_enum        AS l ON (l.id = e.eduid)
  INNER JOIN  profile_education_degree_enum AS d ON (d.degree = "Doctorat")
  INNER JOIN  profile_education_field_enum  AS f ON (f.field = "Médecine")
       WHERE  l.name = "Médecine";

DELETE FROM  profile_education_enum
      WHERE  name = "Médecine";

# vim:set syntax=mysql:

