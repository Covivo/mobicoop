import React from 'react';
import {
  Show,
  TextField,
  SelectField,
  Link,
  Tab,
  TabbedShowLayout,
  Datagrid,
  Button,
  EditButton,
  RichTextField,
  DeleteButton,
  ReferenceManyField,
  useTranslate,
} from 'react-admin';
const statusChoices = [
  { id: 0, name: "En cours d'édition" },
  { id: 1, name: 'En ligne' },
];
const AddParagraphButton = ({ record }) => (
  <Link
    to={{
      pathname: `/paragraphs/create`,
      state: { record: { section: record.id } },
    }}
  >
    <Button label="Ajouter un paragraphe" />
  </Link>
);
const EditParagraphButton = ({ record }) => (
  <Link
    to={{
      pathname: `/paragraphs/${encodeURIComponent(record ? record.id : '')}`,
      state: { section: record && record.section && record.section.id },
    }}
  >
    <Button label="Modifier" />
  </Link>
);
export const SectionShow = (props) => {
  const translate = useTranslate();
  return (
    <Show {...props} title="Articles > afficher une section">
      <TabbedShowLayout>
        <Tab label="Détails">
          <TextField source="originId" label="ID" />
          <TextField
            label={translate('custom.label.article.label.article')}
            source="article.title"
          />
          <SelectField
            source="status"
            label={translate('custom.label.article.label.status')}
            choices={statusChoices}
          />
          <TextField source="title" label={translate('custom.label.article.label.title')} />
          <TextField source="subtitle" label={translate('custom.label.article.label.subtitle')} />
          <TextField source="position" label={translate('custom.label.article.label.position')} />
          <EditButton />
        </Tab>
        <Tab label="Paragraphes" path="paragraphs">
          <ReferenceManyField source="paragraphs" reference="paragraphs" addLabel={false}>
            <Datagrid>
              <RichTextField
                source="text"
                label={translate('custom.label.article.label.content')}
              />
              <TextField
                source="position"
                label={translate('custom.label.article.label.position')}
              />
              <EditParagraphButton />
              <DeleteButton />
            </Datagrid>
          </ReferenceManyField>
          <AddParagraphButton />
        </Tab>
      </TabbedShowLayout>
    </Show>
  );
};
