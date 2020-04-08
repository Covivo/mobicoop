import React, {useState} from 'react';
import GeocompleteInput from "../Utilities/geocomplete";
import { UserRenderer, addressRenderer } from '../Utilities/renderers'
import TerritoryInput from "../Utilities/territory";

import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';

import {
    Edit,
    TabbedForm, FormTab,
    TextInput, SelectInput, DateInput,
    email, regex, ReferenceArrayInput, SelectArrayInput,BooleanInput,ReferenceInput,ReferenceField, FunctionField,useTranslate,useDataProvider
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'

const useStyles = makeStyles({
    spacedHalfwidth: { width:"45%", marginBottom:"1rem", display:'inline-flex', marginRight: '1rem' },
    footer: { marginTop:"2rem" },
});


const UserEdit = props => {

  const Test = ({record}) =>  {
    console.info(record)
  /*  dataProvider.getOne('users',{id:localStorage.getItem('id')} )
        .then( ({ data }) => setUser(data) )
    return null;*/
    return null;
  }
    const dataProvider = useDataProvider();

    const [territory, setTerritory] = useState();
    const [roles, setRoles] = useState([]);
    const [fields, setFields] = useState([{'roles' : [], 'territory' : null}]);


    dataProvider.getList('permissions/roles', {pagination:{ page: 1 , perPage: 1000 }, sort: { field: 'id', order: 'ASC' }, })
      .then( ({ data }) => {
        console.info(data)
        setRoles(data)
      });

    const classes = useStyles();
    const translate = useTranslate();
    const instance = process.env.REACT_APP_INSTANCE_NAME;
    const required = (message = translate('custom.alert.fieldMandatory')) =>
        value => value ? undefined : message;

    const genderChoices = [
        { id: 1, name: translate('custom.label.user.choices.women') },
        { id: 2, name: translate('custom.label.user.choices.men') },
        { id: 3, name: translate('custom.label.user.choices.other') },
    ];
    const smoke = [
        {id : 0, name : translate('custom.label.user.choices.didntSmoke')},
        {id : 1, name : translate('custom.label.user.choices.didntSmokeCar')},
        {id : 2, name : translate('custom.label.user.choices.smoke')},
    ];
    const musique = [
        {id : false, name :  translate('custom.label.user.choices.withoutMusic')},
        {id : true, name : translate('custom.label.user.choices.withMusic')},
    ];

    const bavardage = [
        {id : false, name : translate('custom.label.user.choices.dontTalk')},
        {id : true, name : translate('custom.label.user.choices.talk')},
    ];
    const phoneDisplay = [
        {id : 0, name : translate('custom.label.user.phoneDisplay.forAll')},
        {id : 1, name : translate('custom.label.user.phoneDisplay.forCarpooler')},
    ];

    const validateRequired = [required()];
    const emailRules = [required(), email() ];

    function handleAdd() {
      const values = [...fields];
      values.push({'roles' : [], 'territory' : null});
      setFields(values);
    }

    function handleRemove(i) {
      const values = [...fields];
      values.splice(i, 1);
      setFields(values);
    }


    const handleAddPair = (indice, nature) => e => {
        const values = [...fields];
        if (nature == 'roles')   values[indice]['roles'] = e.target.value;
        else  values[indice]['territory'] = '/territories/'+e.id;
        setFields(values);
    }

    return (
        <Edit { ...props } title={translate('custom.label.user.title.edit')}>


        <TabbedForm initialValues={{news_subscription:true}} >
            <FormTab label={translate('custom.label.user.indentity')}>
                <TextInput fullWidth required source="email" label={translate('custom.label.user.email')} validate={ emailRules }  />

                <TextInput fullWidth required source="familyName" label={translate('custom.label.user.familyName')} validate={ validateRequired } formClassName={classes.spacedHalfwidth} />
                <TextInput fullWidth required source="givenName" label={translate('custom.label.user.givenName')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                <SelectInput required source="gender" label={translate('custom.label.user.gender')} choices={genderChoices} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                <DateInput required source="birthDate" label={translate('custom.label.user.birthDate')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                <TextInput required source="telephone" label={translate('custom.label.user.telephone')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                <BooleanInput fullWidth label={translate('custom.label.user.newsSubscription',{ instanceName: instance })} source="news_subscription" formClassName={classes.spacedHalfwidth} />

                <SelectInput fullWidth source="phoneDisplay" label={translate('custom.label.user.phoneDisplay.visibility')} choices={phoneDisplay}  formClassName={classes.spacedHalfwidth}/>



                <BooleanInput initialValue={true} label={translate('custom.label.user.accepteReceiveEmail')} source="newsSubscription" />

            </FormTab>
            <FormTab label={translate('custom.label.user.preference')}>
                <SelectInput fullWidth source="music" label={translate('custom.label.user.carpoolSetting.music')} choices={musique} formClassName={classes.spacedHalfwidth}/>
                <TextInput fullWidth source="musicFavorites" label={translate('custom.label.user.carpoolSetting.musicFavorites')} formClassName={classes.spacedHalfwidth}/>
                <SelectInput fullWidth source="chat" label={translate('custom.label.user.carpoolSetting.chat')} choices={bavardage} formClassName={classes.spacedHalfwidth}/>
                <TextInput fullWidth source="chatFavorites" label={translate('custom.label.user.carpoolSetting.chatFavorites')} formClassName={classes.spacedHalfwidth}/>
                <SelectInput fullWidth source="smoke" label={translate('custom.label.user.carpoolSetting.smoke')} choices={smoke} formClassName={classes.spacedHalfwidth}/>
            </FormTab>

                  <FormTab label={translate('custom.label.user.manageRoles')}>

                          <Test />
                        <div type="button" onClick={() => handleAdd()}>
                          +
                        </div>

                        {fields.map((field, i) => {

                          return (
                            <div key={`div-${i}`} fullwidth="true">

                              <Select
                                   multiple
                                   onChange={handleAddPair(i, 'roles')}
                                   value={field['roles']}
                                 >
                                  { roles.map( d =>  <MenuItem value={d.id}>{d.name}</MenuItem> ) }
                                 </Select>

                              <TerritoryInput key={`territory-${i}`} source="userTerritories"
                                label={translate('custom.label.user.territory')}  setTerritory={handleAddPair(i, 'territory')}
                                formClassName={classes.spacedHalfwidth} validate={required("L'adresse est obligatoire")}/>

                              <button type="button" onClick={() => handleRemove(i)}>
                                X
                              </button>
                            </div>
                          );
                        })}

                  </FormTab>

        </TabbedForm>
        </Edit>
    );

};
export default UserEdit
