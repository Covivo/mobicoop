import React from 'react';
import { 
    Show,
    SimpleShowLayout,
    TextField, ReferenceField, ReferenceArrayField, SingleFieldList, ChipField,
    EditButton
} from 'react-admin';

export const RoleShow = (props) => (
    <Show { ...props } title="Rôles > afficher">
        <SimpleShowLayout>
            <TextField source="originId" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField source="parent" label="Rôle parent" reference="rights" allowEmpty>
                <TextField source="name" />
            </ReferenceField>
            <ReferenceArrayField source="rights"label="Droits" reference="rights" >
                <SingleFieldList>
                    <ChipField source="name" />
                </SingleFieldList>
            </ReferenceArrayField>
            <EditButton />
        </SimpleShowLayout>
    </Show>
);