import React from 'react';
import { 
    Show,
    SimpleShowLayout,
    TextField, SelectField, ReferenceField, 
    EditButton
} from 'react-admin';

const typeChoices = [
    { id: 1, name: 'Item' },
    { id: 2, name: 'Groupe' },
];

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