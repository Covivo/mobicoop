import React from 'react';
import { 
    Create, Edit, List, Show,
    SimpleForm,
    SimpleShowLayout,
    Datagrid, 
    TextInput, ReferenceInput, SelectInput, ReferenceArrayInput, SelectArrayInput, DisabledInput, 
    regex,
    TextField, ReferenceField, ReferenceArrayField, SingleFieldList, ChipField,
    ShowButton, EditButton,
} from 'react-admin';

const validateName = regex(/^ROLE_[A-Z_]+$/, 'Nom invalide');

// Create
export const RoleCreate = (props) => (
    <Create { ...props }>
        <SimpleForm>
            <TextInput source="title" label="Titre"/>
            <TextInput source="name" label="Nom" validate={validateName} />
            <ReferenceInput label="Rôle parent" source="parent_id" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput label="Droits" source="rights" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Create>
);

// Edit
export const RoleEdit = (props) => (
    <Edit {...props}>
        <SimpleForm>
            <DisabledInput source="originId" label="ID"/>
            <TextInput source="title" label="Titre"/>
            <TextInput source="name" label="Nom" validate={validateName} />
            <ReferenceInput label="Rôle parent" source="parent" reference="roles">
                <SelectInput optionText="title" />
            </ReferenceInput>
            <ReferenceArrayInput label="Droits" source="rights" reference="rights">
                <SelectArrayInput optionText="name" />
            </ReferenceArrayInput>
        </SimpleForm>
    </Edit>
);

// List
export const RoleList = (props) => (
    <List {...props} title="Roles" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Rôle parent" source="parent" reference="roles" allowEmpty>
                <TextField source="title" />
            </ReferenceField>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);

// Show
export const RoleShow = (props) => (
    <Show { ...props }>
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Rôle parent" source="parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <ReferenceArrayField label="Droits" reference="rights" source="rights">
                <SingleFieldList>
                    <ChipField source="name" />
                </SingleFieldList>
            </ReferenceArrayField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);