import React from 'react';
import {
    Show,
    Tab, TabbedShowLayout,
    List,
    Datagrid,
    BooleanField, TextField, DateField, RichTextField, SelectField, ReferenceArrayField, ReferenceField, FunctionField,BulkDeleteButton,useTranslate
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles';
import { Typography, List as ListMaterial, ListItem, ListItemIcon, ListItemText, Card, CardHeader, Divider} from '@material-ui/core'
import AccountBoxIcon from '@material-ui/icons/AccountBox';
import DriveEtaIcon from '@material-ui/icons/DriveEta';
import VerifiedUserIcon from '@material-ui/icons/VerifiedUser';
import TodayIcon from '@material-ui/icons/Today';
import EventAvailableIcon from '@material-ui/icons/EventAvailable';

import EmailComposeButton from "../../Email/EmailComposeButton";
import UserReferenceField from '../../User/UserReferenceField';
import {addressRenderer } from '../../Utilities/renderers'
import {validationChoices, statusChoices } from './communityChoices'
import isAuthorized from '../../../Auth/permissions'

const useStyles = makeStyles({
    actionButton : { marginTop:"1rem", marginBottom:"1rem" },
})


const UserBulkActionButtons = props => (
    <>
        {isAuthorized("mass_create") && <EmailComposeButton label="Email" {...props} /> }
        {/* default bulk delete action */}
        <BulkDeleteButton {...props} />
    </>
);

const Aside = ({ record }) => {
  const translate = useTranslate();
      return (
        <Card style={{ width: 300, marginLeft:'1rem' }} >
            <CardHeader title={<Typography variant="button">Paramètres</Typography>} />
            { record &&
            <ListMaterial>
                <ListItem >
                    <ListItemIcon><AccountBoxIcon /></ListItemIcon>
                    <ListItemText primary={<Typography variant="body2">{record.membersHidden ? translate('custom.label.community.memberHidden') : translate('custom.label.community.memberVisible')}</Typography>} />
                </ListItem>
                <ListItem >
                    <ListItemIcon><DriveEtaIcon /></ListItemIcon>
                    <ListItemText primary={<Typography variant="body2">{record.membersHidden ? translate('custom.label.community.announceHidden') : translate('custom.label.community.announceVisible')}</Typography>} />
                </ListItem>
                <ListItem >
                    <ListItemIcon><VerifiedUserIcon /></ListItemIcon>
                    <ListItemText primary={<Typography variant="body2">{validationChoices.find(e => e.id === (record.validationType || 0)).name }</Typography>} />
                </ListItem>
                <ListItem >
                    <ListItemIcon><TodayIcon /></ListItemIcon>
                    <ListItemText primary={<Typography variant="body2">{translate('custom.label.community.createdAt') + new Date(record.createdDate).toLocaleDateString()}</Typography>} />
                </ListItem>
                <ListItem >
                    <ListItemIcon><EventAvailableIcon /></ListItemIcon>
                    <ListItemText primary={<Typography variant="body2">{record.updatedDate ? translate('custom.label.community.updatedAt') + new Date(record.updatedDate).toLocaleDateString() : translate('custom.label.community.neverUpdate')}</Typography>} />
                </ListItem>
            </ListMaterial>
            }


        </Card>
      )
};

const CommunityTitle = ({ record }) => {
    return <span>Communauté {record ? `"${record.name}"` : ''}</span>;
};

export const CommunityShow = (props) => {
    const translate = useTranslate();
    return (
    <Show { ...props } title={<CommunityTitle />} aside={<Aside />}>
        <TabbedShowLayout>
            <Tab label={translate('custom.label.community.detail')}>
                <TextField source="name" label={translate('custom.label.community.name')} />
                <UserReferenceField label={translate('custom.label.community.createdBy')}  source="user" reference="users" />
                <ReferenceField source="address"  label={translate('custom.label.community.adress')}  reference="addresses" link="">
                    <FunctionField render={addressRenderer} />
                </ReferenceField>
                <TextField source="domain" label={translate('custom.label.community.domainName')} />
                <TextField source="description" label={translate('custom.label.community.description')} />
                <RichTextField source="fullDescription" label={translate('custom.label.community.descriptionFull')} />
                <FunctionField label={translate('custom.label.community.numberMember')}  render={record => console.info(record) } />
            </Tab>
            <Tab label={translate('custom.label.community.membersModerator')}>

                <ReferenceArrayField source="communityUsers" reference="community_users" addLabel={false}>
                    <List {...props}
                          perPage={ 25 }
                          bulkActionButtons={<UserBulkActionButtons />}
                          actions={null}
                          sort={{ field: 'id', order: 'ASC' }}
                          title=": composition"
                          filter={{ is_published: true }}
                    >
                        <Datagrid>
                            <UserReferenceField label={translate('custom.label.community.member')}  source="user" reference="users" />
                            <SelectField source="status" label={translate('custom.label.community.status')}  choices={statusChoices} />
                            <DateField source="createdDate"  label={translate('custom.label.community.joinAt')} />
                            <DateField source="acceptedDate" label={translate('custom.label.community.acceptedAt')}/>
                            <DateField source="refusedDate" label={translate('custom.label.community.refusedAt')}/>
                            { /*
                            Edit and Delete button should be in an Community Edit view
                            <EditButton />
                            <DeleteButton />
                            */ }
                        </Datagrid>
                    </List>
                </ReferenceArrayField>
                { /*  <AddNewMemberButton /> should be in an Community Edit view */ }
            </Tab>
        </TabbedShowLayout>
    </Show>
    )
};
