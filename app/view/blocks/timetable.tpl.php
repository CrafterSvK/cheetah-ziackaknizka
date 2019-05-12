<?php $week = ['Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok']; ?>

	<table id="timetable">
		<thead>
		<tr>
			<th>Deň</th>
			<?php for ($hour = 0; $hour < 10; $hour++) echo "<th>{$hour}. hodina</th>" ?>
		</tr>
		</thead>
		<tbody>
		<?php for ($day = 0; $day < 5; $day++): ?>
			<tr>
				<th><?= $week[$day] ?></th>
				<?php for ($hour = 0; $hour < 10; $hour++): ?>
					<td data-hour="<?= $hour ?>" data-day="<?= $day ?>">
						<?php if (isset($this->lessons[$day][$hour])):
							foreach ($this->lessons[$day][$hour] as $lesson): ?>
								<div data-ttid="<?= $lesson->ttid ?>"
										 data-tlid="<?= $lesson->tlid ?>"
										 data-lid="<?= $lesson->lid ?>"
										 data-tuid="<?= $lesson->tuid ?>"
								><?= $lesson->name ?></div>
							<?php endforeach; endif; ?>
						<?php if ($this->isAdmin()): ?>
							<div class="new" data-ttid="0" data-tlid="0" data-lid="0">+</div>
						<?php endif; ?>
					</td>
				<?php endfor; ?>
			</tr>
		<?php endfor; ?>
		</tbody>
	</table>

<?php if ($this->isAdmin()): ?>
	<div id="timetable-modal" style="display: none">
		<h1></h1>

		<div id="error">

		</div>

		<form method="post">
			<div id="close">✕</div>
			<ul>
				<li><input type="number" name="day" hidden required></li>
				<li><input type="number" name="hour" hidden required></li>
				<li><input type="number" name="tlid" hidden required></li>

				<li><label for="lid">Predmet</label>
					<input list="lids" name="lid" autocomplete="off" placeholder="Predmet" required>
				</li>
				<datalist id="lids">
					<?php foreach ($this->subjects as $subject): ?>
						<option value="<?= $subject['name'] ?>" data-value="<?= $subject['lid'] ?>"></option>
					<?php endforeach; ?>
				</datalist>

				<li><label for="tuid">Učiteľ</label>
					<input list="teachers" name="tuid" autocomplete="off" placeholder="Učiteľ" required>
				</li>
				<datalist id="teachers">
					<?php foreach ($this->teachers as $teacher): ?>
						<option value="<?= $teacher->full_name ?>" data-value="<?= $teacher->uid ?>"></option>
					<?php endforeach; ?>
				</datalist>

				<li><label for="ttid">Trieda alebo skupina</label>
					<select name="ttid" required>
						<option selected value="0" hidden>Zvoľte triedu alebo skupinu</option>
						<?php foreach ($this->timetable->tables as $table): ?>
							<option value="<?= $table['ttid'] ?>"><?= $table['name'] ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li><input type="submit" name="change" value="Uložiť hodinu"></li>
				<li><input type="button" id="move" value="Presunúť a uložiť hodinu"></li>
				<li><input type="submit" name="remove" value="Vymazať hodinu"></li>
			</ul>
		</form>

	</div>
	<script src="/cdn/modal.js"></script>
	<script>
      let teachers = {};
      let subjects = {};
      let table;
      let modal;

      window.onload = () => {
          if (window.history.replaceState) {
              window.history.replaceState(null, null, window.location.href);
          }

          table = document.querySelector('#timetable');

          table.querySelectorAll('tbody td div').forEach(
              el => el.onclick = () => modal = new TimetableModal(el)
          );

          document.querySelectorAll('#teachers option').forEach(el => {
              teachers[el.value] = el.dataset.value;
              teachers[el.dataset.value] = el.value;
          });

          document.querySelectorAll('#lids option').forEach(el => {
              subjects[el.value] = el.dataset.value;
              subjects[el.dataset.value] = el.value;
          });
      };

      class TimetableModal extends Modal {
          constructor(el) {
              let timetable = document.querySelector('#timetable-modal');

              super(timetable);

              this.requiredFields = [];
              this.number = /[0-9]+/;
              this.event = null;

              this.template.querySelector('#move').onclick = () => this.move();
              this.template.querySelector('#close').onclick = () => this.close();

              window.addEventListener('keydown', event => {
                  switch (event.keyCode || event.code) {
                      case 27: {
                          if (this.event === 'MOVE') {
                              this.stopMove();
                          } else {
                              this.close();
                          }
                      }
                      default: {
                          break;
                      }
                  }
              });

              this.getInput('hour').value = el.parentElement.dataset.hour || null;
              this.getInput('day').value = el.parentElement.dataset.day || null;
              this.getInput('remove').style.display = 'inline';

              this.setTitle("Zmeniť hodinu");
              this.getInput('change').value = "Uložiť hodinu";
              this.template.querySelector('#move').style.display = 'inline';
              this.error();

              if (el.dataset.lid == 0 || el.dataset.lid == null) {
                  this.getInput('remove').style.display = 'none';
                  this.template.querySelector('#move').style.display = 'none';

                  this.setTitle("Pridať hodinu");
                  this.getInput('change').value = "Pridať hodinu";
              }

              this.getInput('tlid').value = el.dataset.tlid || 0;

              this.getInput('ttid').value = el.dataset.ttid || 0;
              this.getInput('lid').value = subjects[el.dataset.lid] || null;
              this.getInput('tuid').value = teachers[el.dataset.tuid] || null;

              this.template.style.display = 'block';

              this.template.querySelector('form').onsubmit = () => this.submit();

              console.log(this.template.querySelector('form'));

              this.template.querySelectorAll('input[type="submit"]').forEach(el => el.onclick = () => this.submitted(el));

              this.template.querySelectorAll('input[required=""], select[required=""]').forEach(el => {
                  this.requiredFields.push(el);
              });

              this.template.querySelectorAll(`datalist option`).forEach(option => option.hidden = null);

              let hid = TimetableModal.timeToHID(this.getInput('day').value, this.getInput('hour').value);

              this.getUnavailable(hid);

              this.open();
          }

          getUnavailable(hid) {
              let that = this;
              fetch(`/api/teacher/${hid}/unavailable`).then(data => data.json().then(json => that.unavailable = json));
          }

          submitted(el) {
              this.lastSubmitButton = el.name;

              let require = "";

              if (this.lastSubmitButton === 'change') require = "required";
              else if (this.lastSubmitButton === 'remove') require = null;

              this.requiredFields.forEach(el => el.required = require);
          }

          setTitle(title = null) {
              this.template.getElementsByTagName('h1')[0].innerHTML = title;
          }

          async move() {
              let tuid = teachers[this.getInput('tuid').value];

              if (this.number.test(tuid) && tuid != 0) {
                  let lessons = await fetch(`/api/teacher/${tuid}/lessons`).then(data => data.json().then(json => json));

                  table.querySelectorAll('tbody td').forEach(td => {
                      let hid = TimetableModal.timeToHID(td.dataset.day, td.dataset.hour);

                      let overlay = document.createElement('div');
                      overlay.classList.add('overlay');

                      if (typeof lessons[hid] === 'undefined') {
                          overlay.classList.add('able');
                          overlay.onclick = event => this.stopMove(event);
                      } else {
                          overlay.classList.add('unable');
                      }

                      td.appendChild(overlay);
                  });

                  this.event = 'MOVE';
                  this.close();
              } else {
                  return this.error("Zadali ste neplatného učiteľa.");
              }
          }

          static timeToHID(day, hour) {
              return (10 * Number(day) + 1) + Number(hour);
          }

          stopMove(event = null) {
              if (event !== null) {
                  if (this.validate(true)) {
                      this.getInput('day').value = event.target.parentElement.dataset.day;
                      this.getInput('hour').value = event.target.parentElement.dataset.hour;

                      this.getInput('change').click();
                  } else {
                      this.error("Pred presunutím musia byť informácie správne.");
                  }
              }

              table.querySelectorAll('.overlay').forEach(el => el.parentElement.removeChild(el));

              this.event = null;
              this.open();
          }

          error(error = null) {
              this.template.querySelector('#error').innerHTML = error === null ? null : `<p>${error}</p>`;

              return false;
          }

          validate(move = false) {
              let tlid = this.getInput('tlid').value;
              let hour = this.getInput('hour').value;
              let day = this.getInput('day').value;

              if (!this.number.test(hour) || !(hour <= 8 && hour >= 0))
                  return this.error("Neočakávaná chyba, obnovte stránku.");

              if (!this.number.test(day) || !(day <= 4 && day >= 0))
                  return this.error("Neočakávaná chyba, obnovte stránku.");

              if (this.lastSubmitButton === 'change' || move) {
                  let lid = this.getInput('lid').value;
                  let teacher = this.getInput('tuid').value;
                  let ttid = this.getInput('ttid').value;

                  if (!this.number.test(subjects[lid]))
                      return this.error("Zadali ste neplatnú hodinu.");

                  if (!this.number.test(tlid))
                      return this.error("Neočakávaná chyba, obnovte stránku.");

                  if (!this.number.test(teachers[teacher]))
                      return this.error("Zadali ste neplatného učiteľa.");

                  if (typeof this.unavailable !== "object")
                      return this.error("Formulár sa stále načítava alebo nastala chyba.");

                  if (this.unavailable.attributeOf(teachers[teacher]) !== -1 &&
											this.unavailable.attributeOf(teachers[teacher]) !== tlid)
                      return this.error("Učiteľ má túto hodinu obsadenú.");

                  if (!this.number.test(ttid))
                      return this.error("Neočakávana chyba, obnovte stránku.");

                  return true;
              } else if (this.lastSubmitButton === 'remove') {
                  if (!this.number.test(tlid) || tlid == 0)
                      return this.error("Nevytvorená hodina sa nedá odstrániť.");

                  return true;
              } else {
                  return false;
              }
          }

          submit() {
              if (this.validate()) {
                  if (this.lastSubmitButton === 'change') {
                      if (window.confirm("Ste si istý?")) {
                          let tuid = this.getInput('tuid').value;
                          this.getInput('tuid').value = teachers[tuid];

                          let lid = this.getInput('lid').value;
                          this.getInput('lid').value = subjects[lid];

                          return true;
                      }
                  } else if (this.lastSubmitButton === 'remove') {
                      return window.confirm("Ste si istý?");
                  }
              }

              return false;
          };
      }
	</script>
<?php endif; ?>