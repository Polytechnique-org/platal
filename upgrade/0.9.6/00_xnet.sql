update groupex.asso set nom=replace(nom, '<small>', '');
update groupex.asso set nom=replace(nom, '</small>', '');
alter table groupex.asso drop column m_list;
