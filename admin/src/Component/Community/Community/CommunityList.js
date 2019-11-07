import React from 'react';
import { 
    List,
    Datagrid,
    TextInput,
    ShowButton, EditButton,
    BooleanField, TextField, DateField, SelectField,
    Filter
} from 'react-admin';
const validationChoices = [
    { id: 0, name: 'Validation automatique' },
    { id: 1, name: 'Validation manuelle' },
    { id: 2, name: 'Validation par le domaine' },
];

const CommunityFilter = (props) => (
    <Filter {...props}>
        <TextInput source="name" label="Nom" alwaysOn />
    </Filter>
);
const CommunityPanel = ({ id, record, resource }) => (
    <div dangerouslySetInnerHTML={{ __html: record.fullDescription }} />
);
export const CommunityList = (props) => (
    <List {...props} title="Communautés > liste" perPage={ 25 } filters={<CommunityFilter />} sort={{ field: 'originId', order: 'ASC' }}>
        <Datagrid expand={<CommunityPanel />}>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <BooleanField source="membersHidden" label="Membres masqués" sortable={false} />
            <BooleanField source="proposalsHidden" label="Annonces masquées" sortable={false} />
            <TextField source="description" label="Description"/>
            <DateField source="createdDate" label="Date de création"/>
            <SelectField source="validationType" label="Type de validation" choices={validationChoices} />
            <TextField source="domain" label="Nom de domaine"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);