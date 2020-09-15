import React from 'react';
import RichTextInput from 'ra-input-rich-text';

import {
  Edit,
  SimpleForm,
  required,
  SelectInput,
  NumberInput,
  TextField,
  useTranslate,
} from 'react-admin';

export const ParagraphEdit = (props) => {
  const translate = useTranslate();
  const statusChoices = [
    { id: 0, name: translate('custom.label.article.label.draft') },
    { id: 1, name: translate('custom.label.article.label.published') },
  ];

  const section = props.location.state && props.location.state.section;
  const sectionUri = encodeURIComponent(section);
  const redirect = section ? `/sections/${sectionUri}/show/paragraphs` : 'show';

  return (
    <Edit
      {...props}
      title={translate('custom.label.article.title.edit_paragraph')}
      undoable={false}
    >
      <SimpleForm redirect={redirect}>
        <TextField label={translate('custom.label.article.label.section')} source="section.title" />
        <SelectInput
          source="status"
          label={translate('custom.label.article.label.status')}
          choices={statusChoices}
        />
        <RichTextInput
          source="text"
          label={translate('custom.label.article.label.content')}
          validate={required()}
        />
        <NumberInput source="position" label={translate('custom.label.article.label.position')} />
      </SimpleForm>
    </Edit>
  );
};
