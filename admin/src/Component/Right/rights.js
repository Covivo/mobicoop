import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm, 
    SimpleShowLayout,
    Datagrid, 
    TextInput, SelectInput, ReferenceInput, DisabledInput, 
    Filter, required,
    TextField, SelectField, ReferenceField, 
    ShowButton, EditButton,
} from 'react-admin';

const typeChoices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

// List
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

// Show
export const RightShow = (props) => (
    <Show { ...props } title="Droits > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <SelectField source="type" label="Type" choices={typeChoices} sortable={false} />
            <TextField source="name" label="Nom"/>
            <ReferenceField source="parent" label="Groupe" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);

// Create
export const RightCreate = (props) => (
    <Create { ...props } title="Droits > ajouter">
        <SimpleForm>
            <SelectInput source="type" label="Type" choices={typeChoices} validate={required()} />
            <TextInput source="name" label="Nom" validate={required()} />
            <ReferenceInput source="parent" label="Groupe" reference="rights" filter={{ type: 2 }}>
                <SelectInput optionText="name" />
            </ReferenceInput>
        </SimpleForm>
    </Create>
);

// Edit
export const RightEdit = (props) => (
    <Edit {...props} title="Droits > éditer">
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <SelectInput source="type" label="Type" choices={typeChoices} validate={required()} />
            <TextInput source="name" label="Nom" validate={required()} />
            <ReferenceInput source="parent" label="Groupe" reference="rights" filter={{ type: 2 }}>
                <SelectInput optionText="name" />
            </ReferenceInput>
        </SimpleForm>
    </Edit>
);