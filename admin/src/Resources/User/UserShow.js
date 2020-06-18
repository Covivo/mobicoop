import React from 'react';
import EmailIcon from '@material-ui/icons/Email';
import PhoneIcon from '@material-ui/icons/Phone';
import CheckIcon from '@material-ui/icons/Check';
import ClearIcon from '@material-ui/icons/Clear';

import {
  Show,
  TabbedShowLayout,
  Tab,
  TextField,
  DateField,
  FunctionField,
  Labeled,
  ReferenceArrayField,
  BooleanField,
  Datagrid,
  ReferenceField,
  ReferenceManyField,
  useTranslate,
  SelectField,
} from 'react-admin';

import {
  Typography,
  List as ListMaterial,
  ListItem,
  ListItemIcon,
  ListItemText,
  Card,
  CardHeader,
} from '@material-ui/core';

import { addressRenderer } from '../../utils/renderers';
import isAuthorized from '../../auth/permissions';
import { ReferenceRecordIdMapper } from '../../components/utils/ReferenceRecordIdMapper';
import { formatPhone } from '../Solidary/SolidaryUserBeneficiary/Fields/PhoneField';

const renderSmoke = (smoke) => {
  let text = '';
  switch (smoke) {
    case 0:
      text = 'Je ne fume pas';
      break;
    case 1:
      text = 'Je ne fume pas en voiture';
      break;
    case 2:
      text = 'Je fume';
      break;
    default:
      text = '';
  }
  return text;
};

const renderMusic = (music) => {
  let text = '';
  switch (music) {
    case true:
      text = 'Je préfère rouler sans fond sonore';
      break;
    case false:
      text = 'Je préfère rouler sans fond sonore';
      break;
    default:
      text = '';
  }
  return text;
};

const renderChat = (chat) => {
  let text = '';
  switch (chat) {
    case true:
      text = 'Je ne suis pas bavard';
      break;
    case false:
      text = 'Je discute';
      break;
    default:
      text = '';
  }
  return text;
};

const renderCommunityUserStatus = (status) => {
  let text = '';
  switch (status) {
    case 0:
      text = 'En attente';
      break;
    case 1:
      text = 'Membre';
      break;
    case 2:
      text = 'Modérateur';
      break;
    case 3:
      text = 'Refusé';
      break;
    default:
      text = 'Inconnu';
  }
  return text;
};

const ConditionalMusicFavoritesField = ({ record, ...rest }) =>
  record && record.musicFavorites ? (
    <Labeled label="Musiques favorites : ">
      <TextField source="musicFavorites" record={record} {...rest} />
    </Labeled>
  ) : null;

const ConditionalChatFavoritesField = ({ record, ...rest }) =>
  record && record.chatFavorites ? (
    <Labeled label="Sujets favoris : ">
      <TextField source="musicFavorites" record={record} {...rest} />
    </Labeled>
  ) : null;

const UserShow = (props) => {
  const translate = useTranslate();
  const required = (message = translate('custom.alert.fieldMandatory')) => (value) =>
    value ? undefined : message;

  const genderChoices = [
    { id: 1, name: translate('custom.label.user.choices.women') },
    { id: 2, name: translate('custom.label.user.choices.men') },
    { id: 3, name: translate('custom.label.user.choices.other') },
  ];
  const smoke = [
    { id: 0, name: translate('custom.label.user.choices.didntSmoke') },
    { id: 1, name: translate('custom.label.user.choices.didntSmokeCar') },
    { id: 2, name: translate('custom.label.user.choices.smoke') },
  ];
  const musique = [
    { id: false, name: translate('custom.label.user.choices.withoutMusic') },
    { id: true, name: translate('custom.label.user.choices.withMusic') },
  ];

  const bavardage = [
    { id: false, name: translate('custom.label.user.choices.dontTalk') },
    { id: true, name: translate('custom.label.user.choices.talk') },
  ];
  const phoneDisplay = [
    { id: 0, name: translate('custom.label.user.phoneDisplay.forAll') },
    { id: 1, name: translate('custom.label.user.phoneDisplay.forCarpooler') },
  ];

  const validateRequired = [required()];

  const Aside = ({ record }) => {
    const translate = useTranslate();
    return (
      <Card style={{ width: 300, marginLeft: '1rem' }}>
        <CardHeader
          title={<Typography variant="button">{translate('custom.label.user.contact')}</Typography>}
        />
        {record && (
          <ListMaterial>
            <ListItem>
              <ListItemIcon>
                <EmailIcon />
              </ListItemIcon>
              <ListItemText primary={<Typography variant="body2">{record.email}</Typography>} />
            </ListItem>
            <ListItem>
              <ListItemIcon>
                <PhoneIcon />
              </ListItemIcon>
              <ListItemText
                primary={
                  <Typography variant="body2">
                    {record.telephone ? formatPhone(record.telephone) : record.telephone}
                  </Typography>
                }
              />
            </ListItem>
            <ListItem>
              <ListItemIcon>{record.newsSubscription ? <CheckIcon /> : <ClearIcon />}</ListItemIcon>
              <ListItemText
                primary={
                  <Typography variant="body2">
                    {record.newsSubscription
                      ? translate('custom.label.user.acceptActualite')
                      : translate('custom.label.user.declineActualite')}
                  </Typography>
                }
              />
            </ListItem>
          </ListMaterial>
        )}
      </Card>
    );
  };

  return (
    <Show {...props} title="Utilisateurs > afficher" aside={<Aside />}>
      <TabbedShowLayout>
        <Tab label="Identité">
          <TextField
            fullWidth
            required
            source="familyName"
            label={translate('custom.label.user.familyName')}
          />
          <TextField
            fullWidth
            required
            source="givenName"
            label={translate('custom.label.user.givenName')}
          />
          <SelectField
            required
            source="gender"
            label={translate('custom.label.user.gender')}
            choices={genderChoices}
          />
          <DateField required source="birthDate" label={translate('custom.label.user.birthDate')} />

          <SelectField
            fullWidth
            source="phoneDisplay"
            label={translate('custom.label.user.phoneDisplay.visibility')}
            choices={phoneDisplay}
          />
          <FunctionField
            label={translate('custom.label.user.currentAdresse')}
            source="addresses"
            render={({ addresses }) => addresses.map(addressRenderer)}
          />
        </Tab>
        <Tab label="Préférences">
          <FunctionField
            label="En ce qui concerne le tabac en voiture..."
            render={(record) => renderSmoke(record.smoke)}
          />
          <FunctionField
            label="En ce qui concerne la musique en voiture.."
            render={(record) => renderMusic(record.music)}
          />
          <ConditionalMusicFavoritesField />
          <FunctionField
            label="En ce qui concerne le bavardage en voiture..."
            render={(record) => renderChat(record.chat)}
          />
          <ConditionalChatFavoritesField />
        </Tab>
        <Tab label="Adresses">
          <ReferenceRecordIdMapper attribute="addresses">
            <ReferenceArrayField source="addresses" reference="addresses" addLabel={false}>
              <Datagrid>
                <BooleanField source="home" label="Domicile" />
                <TextField source="name" label="Nom" />
                <FunctionField
                  label="Address"
                  render={(address) =>
                    address.houseNumber && address.street
                      ? address.houseNumber + ' ' + address.street
                      : address.streetAddress
                  }
                />
                <TextField source="postalCode" label="Code postal" />
                <TextField source="addressLocality" label="Ville" />
                <TextField source="addressCountry" label="Pays" />
              </Datagrid>
            </ReferenceArrayField>
          </ReferenceRecordIdMapper>
        </Tab>
        <Tab label="Communautés">
          <ReferenceManyField
            reference="community_users"
            target="user.id"
            source="originId"
            addLabel={false}
          >
            <Datagrid>
              <ReferenceField reference="communities" source="community" label="Nom">
                <TextField source="name" label="Nom" />
              </ReferenceField>
              <FunctionField
                render={(record) => renderCommunityUserStatus(record.status)}
                label="Status"
              />
              <DateField source="acceptedDate" label="Date d'acceptation" />
              <DateField source="updatedDate" label="Date de mise à jour" />
            </Datagrid>
          </ReferenceManyField>
        </Tab>
        {isAuthorized('permission_manage') && <Tab label="Droits" />}
      </TabbedShowLayout>
    </Show>
  );
};
export default UserShow;
