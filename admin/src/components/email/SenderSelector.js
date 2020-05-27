import React, { useEffect, useState } from 'react';
import { useDataProvider } from 'react-admin';
import { RadioGroup, FormControlLabel, Radio } from '@material-ui/core';

const SenderSelector = ({ onExpediteurChange }) => {
  const expediteurParDefaut = {
    replyTo: process.env.REACT_APP_SENDER_EMAIL_DEFAULT,
    fromName: process.env.REACT_APP_SENDER_FROM_NAME_DEFAULT,
    originId: 0,
  }; // Should be in config file
  const [expediteurs, setExpediteurs] = useState([]);
  const [choix, setChoix] = useState(0);
  const dataProvider = useDataProvider();
  const userId = `/users/ ${localStorage.getItem('id')}`;

  useEffect(() => {
    localStorage.getItem('id') &&
      dataProvider
        .getOne('users', { id: userId })
        .then(({ data }) => {
          const expediteurConnecte = {
            replyTo: data.email,
            fromName: `${data.givenName} ${data.familyName}`,
            id: data.id,
            originId: data.originId,
          };
          setExpediteurs([{ ...expediteurParDefaut, id: data.id }, expediteurConnecte]);
          onExpediteurChange({ ...expediteurParDefaut, id: data.id });
        })
        .catch((error) => {
          setExpediteurs([expediteurParDefaut]);
          console.log("Erreur lors de la recherche de l'utilisateur courant :", error);
        });
    return;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const changeChoix = (e) => {
    const nouveauChoix = parseInt(e.target.value, 10);
    setChoix(nouveauChoix);
    onExpediteurChange(expediteurs[nouveauChoix]);
  };

  if (expediteurs.length === 0) return <p>Chargement des expéditeurs...</p>;
  return (
    <RadioGroup row aria-label="expediteur" name="expediteur" value={choix} onChange={changeChoix}>
      {expediteurs.map((expediteur, indice) => (
        <FormControlLabel
          key={expediteur.originId}
          value={indice}
          control={<Radio />}
          label={`${expediteur.fromName} < ${expediteur.replyTo} >`}
        />
      ))}
    </RadioGroup>
  );
};

export default SenderSelector;
