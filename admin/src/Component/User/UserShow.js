import React from 'react';
import isAuthorized from '../Utilities/authorization';

//import bcrypt from 'bcryptjs';

import { 
    Show,
    TabbedShowLayout, Tab,
    TextField, EmailField, DateField, 
    FunctionField, Labeled,
    ReferenceArrayField, BooleanField, Datagrid,
    ReferenceField, ReferenceManyField
} from 'react-admin';


const renderSmoke = (smoke) => {
    let text = "";
    switch(smoke){
        case 0: text="Je ne fume pas";break;
        case 1: text="Je ne fume pas en voiture";break;
        case 2: text="Je fume";break;
        default: text="";
    }
    return text;
}

const renderMusic = (music) => {
    let text = "";
    switch(music){
        case true: text="Je préfère rouler sans fond sonore";break;
        case false: text="Je préfère rouler sans fond sonore";break;
        default: text="";
    }
    return text;
}

const renderChat = (chat) => {
    let text = "";
    switch(chat){
        case true: text="Je ne suis pas bavard";break;
        case false: text="Je discute";break;
        default: text="";
    }
    return text;
}

const renderCommunityUserStatus = (status) => {
    let text = "";
    switch(status){
        case 0: text="En attente";break;
        case 1: text="Membre";break;
        case 2: text="Modérateur";break;
        case 3: text="Refusé";break;
        default: text="Inconnu";
    }
    return text;
}

const ConditionalMusicFavoritesField = ({ record, ...rest }) =>
record && record.musicFavorites
    ? <Labeled label="Musiques favorites : "><TextField source="musicFavorites" record={record} {...rest} /></Labeled>
    : null;

const ConditionalChatFavoritesField = ({ record, ...rest }) =>
record && record.chatFavorites
    ? <Labeled label="Sujets favoris : "><TextField source="musicFavorites" record={record} {...rest} /></Labeled>
    : null;

export const UserShow = (props) => (
    <Show { ...props } title="Utilisateurs > afficher">
        <TabbedShowLayout>
            <Tab label="Identité">
                <TextField source="originId" label="ID"/>
                <TextField source="givenName" label="Prénom"/>
                <TextField source="familyName" label="Nom"/>
                <EmailField source="email" label="Email" />
                <DateField source="createdDate" label="Date de création"/>
            </Tab>
            <Tab label="Préférences">
                <FunctionField label="En ce qui concerne le tabac en voiture..." render={record=>renderSmoke(record.smoke)}/>
                <FunctionField label="En ce qui concerne la musique en voiture.." render={record=>renderMusic(record.music)}/>
                <ConditionalMusicFavoritesField />
                <FunctionField label="En ce qui concerne le bavardage en voiture..." render={record=>renderChat(record.chat)}/>
                <ConditionalChatFavoritesField />
            </Tab>
            <Tab label="Adresses">
                <ReferenceArrayField source="addresses" reference="addresses" addLabel={false}>
                    <Datagrid>
                        <BooleanField source="home" label="Domicile" />
                        <TextField source="name" label="Nom" />
                        <FunctionField label="Address" render={address => ((address.houseNumber && address.street) ? (address.houseNumber+ ' '+address.street) : address.streetAddress)} />
                        <TextField source="postalCode" label="Code postal" />
                        <TextField source="addressLocality" label="Ville" />
                        <TextField source="addressCountry" label="Pays" />
                    </Datagrid>
                </ReferenceArrayField>
            </Tab>
            <Tab label="Communautés">
                <ReferenceManyField reference="community_users" target="user.id" source="originId" addLabel={false}>
                    <Datagrid>
                        <ReferenceField reference="communities" source="community" label="Nom">
                                <TextField source="name" label="Nom" />
                        </ReferenceField>
                        <FunctionField render={record=>renderCommunityUserStatus(record.status)} label="Status"/>
                        <DateField source="acceptedDate" label="Date d'acceptation"/>
                        <DateField source="updatedDate" label="Date de mise à jour"/>
                    </Datagrid>
                </ReferenceManyField>
            </Tab>
            {isAuthorized("permission_manage") && 
            <Tab label="Droits">
                
            </Tab>}
        </TabbedShowLayout>
    </Show>
);