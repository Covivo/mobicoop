import React from 'react';
import { parse } from 'query-string';

import {
  Create,
  SimpleForm,
  required,
  ReferenceInput,
  SelectInput,
  TextInput,
  NumberInput,
  useTranslate,
} from 'react-admin';

export const SectionCreate = (props) => {
  const translate = useTranslate();
  const statusChoices = [
    { id: 0, name: translate('custom.label.article.label.draft') },
    { id: 1, name: translate('custom.label.article.label.published') },
  ];
  const article =
    props.location.state && props.location.state.record && props.location.state.record.article;
  const articleUri = encodeURIComponent(article);
  const redirect = article ? `/articles/${articleUri}/show` : 'show';

  return (
    <Create {...props} title={translate('custom.label.article.title.add_section')}>
      <SimpleForm defaultValue={{ article }} redirect={redirect}>
        <ReferenceInput
          source="article"
          label={translate('custom.label.article.label.article')}
          reference="articles"
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
        <TextInput source="title" label={translate('custom.label.article.label.title')} />
        <TextInput source="subTitle" label={translate('custom.label.article.label.subtitle')} />
        <NumberInput source="position" label={translate('custom.label.article.label.position')} />
      </SimpleForm>
    </Create>
  );
};
