import React from 'react';
import { 
    Show,
    FormTab, 
    TabbedShowLayout,
    TextField, BooleanField, ReferenceField, SelectField, ReferenceArrayField, SingleFieldList, ChipField, NumberField, RichTextField, FunctionField  
} from 'react-admin';

const userOptionRenderer = choice => `${choice.givenName} ${choice.familyName}`;
const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Actif' },
    { id: 2, name: 'Inactif' },
];  

const addressRenderer = address => `${address.displayLabel[0]} - ${address.displayLabel[1]}`;

export const RelayPointShow = (props) => (
    <Show { ...props } title="Points relais > afficher">
        <TabbedShowLayout>
            <FormTab label="Identité">
                <ReferenceField source="user" label="Créateur" reference="users" >
                    <FunctionField render={userOptionRenderer}/>
                </ReferenceField>
                <TextField source="name" label="Nom" />
                <ReferenceField source="address" label="Adresse" reference="addresses" linkType="">
                    <FunctionField render={addressRenderer} />
                </ReferenceField>
                <SelectField source="status" label="Status" choices={statusChoices} />
                <ReferenceArrayField source="relayPointTypes" label="Types" reference="relay_point_types">
                    <SingleFieldList>
                        <ChipField source="name" />
                    </SingleFieldList>
                </ReferenceArrayField>
                <TextField source="description" label="Description" />
                <RichTextField source="fullDescription" label="Description complète" />
            </FormTab>
            <FormTab label="Communauté">
                <ReferenceField source="community" label="Communauté" reference="communities" allowEmpty>
                    <TextField source="name" />
                </ReferenceField>
                <BooleanField source="private" label="Privé à cette communauté" />
            </FormTab>
            <FormTab label="Propriétés">
                <NumberField source="places" label="Nombre de places" />
                <NumberField source="placesDisabled" label="Nombre de places handicapés" />
                <BooleanField source="free" label="Gratuit" />
                <BooleanField source="secured" label="Sécurisé" />
                <BooleanField source="official" label="Officiel" />
                <BooleanField source="suggested" label="Suggestion autocomplétion" />
            </FormTab>
        </TabbedShowLayout>
    </Show>
);