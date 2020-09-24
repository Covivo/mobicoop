import React from 'react';
import RichTextInput from 'ra-input-rich-text';

import {
  Create,
  SimpleForm,
  required,
  ReferenceInput,
  SelectInput,
  NumberInput,
  useTranslate,
} from 'react-admin';

export const ParagraphCreate = (props) => {
  const translate = useTranslate();
  const statusChoices = [
    { id: 0, name: translate('custom.label.article.label.draft') },
    { id: 1, name: translate('custom.label.article.label.published') },
  ];
  const section =
    props.location.state && props.location.state.record && props.location.state.record.section;
  const sectionUri = encodeURIComponent(section);
  const redirect = section ? `/sections/${sectionUri}/show/paragraphs` : 'show';

  return (
    <Create {...props} title={translate('custom.label.article.title.add_paragraph')}>
      <SimpleForm defaultValue={{ section }} redirect={redirect}>
        <ReferenceInput
          source="section"
          label={translate('custom.label.article.label.section')}
          reference="sections"
          validate={required()}
        >
          <SelectInput optionText="title" />
        </ReferenceInput>
        <SelectInput
          source="status"
          label={translate('custom.label.article.label.status')}
          choices={statusChoices}
          defaultValue={0}
          validate={required()}
        />
        <RichTextInput
          source="text"
          label={translate('custom.label.article.label.content')}
          validate={required()}
        />
        <NumberInput source="position" label={translate('custom.label.article.label.position')} />
      </SimpleForm>
    </Create>
  );
};
