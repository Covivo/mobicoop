import React from 'react';
import { Link, ChipField } from 'react-admin';

const SectionsField = ({ record, ...rest }) => {
  const sections = record ? record.sections : [];
  const editSectionUrl = (section) => section && `/sections/${encodeURIComponent(section.id)}/show`;

  return sections.map((section) => (
    <Link to={editSectionUrl(section)} key={section.id}>
      <ChipField record={section} source="title" />
    </Link>
  ));
};

export default SectionsField;
