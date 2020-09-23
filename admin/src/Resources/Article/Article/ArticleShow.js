import React from 'react';

import {
  Show,
  Tab,
  TabbedShowLayout,
  Link,
  Datagrid,
  Button,
  ShowButton,
  EditButton,
  DeleteButton,
  TextField,
  ArrayField,
  SelectField,
  useTranslate,
} from 'react-admin';

const AddSectionButton = ({ record, translate }) => (
  <Link
    to={{
      pathname: `/sections/create`,
      state: { record: { article: record.id } },
    }}
  >
    <Button label={translate('custom.label.article.action.add_section')} />
  </Link>
);
export const ArticleShow = (props) => {
  const translate = useTranslate();
  const statusChoices = [
    { id: 0, name: translate('custom.label.article.label.draft') },
    { id: 1, name: translate('custom.label.article.label.published') },
  ];
  return (
    <Show {...props} title={translate('custom.label.article.title.show')}>
      <TabbedShowLayout>
        <Tab label="Détails">
          <TextField source="originId" label="ID" />
          <TextField source="title" label={translate('custom.label.article.label.title')} />
          <SelectField
            source="status"
            label={translate('custom.label.article.label.status')}
            choices={statusChoices}
          />
          <EditButton />
        </Tab>
        <Tab label="Sections" path="sections">
          <ArrayField source="sections" reference="sections" addLabel={false}>
            <Datagrid>
              <TextField source="title" label={translate('custom.label.article.label.title')} />
              <TextField
                source="subTitle"
                label={translate('custom.label.article.label.subtitle')}
              />
              <TextField
                source="position"
                label={translate('custom.label.article.label.position')}
              />
              <SelectField
                source="status"
                label={translate('custom.label.article.label.status')}
                choices={statusChoices}
              />
              <ShowButton basePath="/sections" />
              <EditButton />
              <DeleteButton />
            </Datagrid>
          </ArrayField>
          <AddSectionButton translate={translate} />
        </Tab>
      </TabbedShowLayout>
    </Show>
  );
};
