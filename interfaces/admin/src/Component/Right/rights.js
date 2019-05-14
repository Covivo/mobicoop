import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid, 
    TextInput, SelectInput, ReferenceInput, DisabledInput, 
    Filter, 
    TextField, SelectField, ReferenceField, 
    ShowButton, EditButton,
} from 'react-admin';

const typeChoices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

// Create
export const RightCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <SelectInput source="type" label="Type" choices={typeChoices} />
            <TextInput source="name" label="Nom" />
            <ReferenceInput label="Groupe" source="parent" reference="rights" filter={{ type: 2 }}>
                <SelectInput optionText="name" />
            </ReferenceInput>
        </SimpleForm>
    </Create>
);

// Edit
export const RightEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <SelectInput label="Type" source="type" choices={typeChoices} />
            <TextInput source="name" label="Nom" />
            <ReferenceInput label="Groupe" source="parent" reference="rights" filter={{ type: 2 }}>
                <SelectInput optionText="name" />
            </ReferenceInput>
        </SimpleForm>
    </Edit>
);

// List
const RightFilter = (props) => (
    <Filter {...props}>
        <SelectInput label="Type" source="type" choices={typeChoices} allowEmpty={false} alwaysOn resettable />
    </Filter>
);

export const RightList = (props) => (
    <List {...props} title="Rights" perPage={ 30 } filters={<RightFilter />}>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <SelectField label="Type" source="type" choices={typeChoices} sortable={false} />
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Groupe" source="parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const RightShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <SelectField label="Type" source="type" choices={typeChoices} sortable={false} />
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Groupe" source="parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);