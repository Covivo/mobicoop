import React from 'react';

import { List, Datagrid, ShowButton, TextField, SelectField, useTranslate } from 'react-admin';

import SectionsField from './SectionsField';

import { defaultExporterFunctionSuperAdmin } from '../../../utils/utils';

export const ArticleList = (props) => {
  const translate = useTranslate();
  const statusChoices = [
    { id: 0, name: translate('custom.label.article.label.draft') },
    { id: 1, name: translate('custom.label.article.label.published') },
  ];
  return (
    <List
      {...props}
      title={translate('custom.label.article.title.list')}
      exporter={defaultExporterFunctionSuperAdmin()}
      perPage={25}
      sort={{ field: 'originId', order: 'ASC' }}
    >
      <Datagrid>
        <TextField source="originId" label="ID" sortBy="id" />
        <TextField source="title" label={translate('custom.label.article.label.title')} />
        <SelectField
          source="status"
          label={translate('custom.label.article.label.status')}
          choices={statusChoices}
        />
        <SectionsField />
        <ShowButton />
      </Datagrid>
    </List>
  );
};
