import React from 'react';
import { 
    List,
    Datagrid, 
    TextInput, SelectInput,
    Filter,
    TextField, SelectField, ReferenceField, 
    ShowButton, EditButton
} from 'react-admin';

const typeChoices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

const RightFilter = (props) => (
    <Filter {...props}>
        <SelectInput source="type" label="Type" choices={typeChoices} allowEmpty={false} alwaysOn resettable />
        <TextInput source="name" label="Nom" alwaysOn />
    </Filter>
);

export const RightList = (props) => (
    <List {...props} title="Droits > liste" perPage={ 25 } filters={<RightFilter />}>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id" />
            <SelectField source="type" label="Type" choices={typeChoices} sortable={false} />
            <TextField source="name" label="Nom"/>
            <ReferenceField source="parent" label="Groupe" reference="rights" allowEmpty sortable={false}>
                <TextField source="name" />
            </ReferenceField>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);