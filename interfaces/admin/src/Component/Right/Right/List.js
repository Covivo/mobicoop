import React from 'react';
import { Filter, SelectInput, List, Datagrid, TextField, SelectField, ReferenceField, ShowButton, EditButton } from 'react-admin';

const choices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

const RightFilter = (props) => (
    <Filter {...props}>
        <SelectInput label="Type" source="type" choices={choices} allowEmpty={false} alwaysOn resettable />
    </Filter>
);

export const RightList = (props) => (
    <List {...props} title="Rights" perPage={ 30 } filters={<RightFilter />}>
        <Datagrid>
            <TextField source="id" label="ID"/>
            <SelectField label="Type" source="type" choices={choices} sortable={false} />
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Groupe" source="parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);