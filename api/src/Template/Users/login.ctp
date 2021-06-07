<div class="login_wrapper">
    <div class="animate form login_form">
        <section class="login_content">
            <?= $this->Form->create(null) ?>
                <h1>Login Form</h1>
                <div>
                    <input type="text" name="username" class="form-control" placeholder="Username" required="" />
                </div>
                <div>
                    <input type="password" name="password" class="form-control" placeholder="Password" required="" />
                </div>
                <div>
                    <button type="submit" class="btn btn-default">Log in</button>
                </div>
            <?= $this->Form->end() ?>
        </section>
    </div>
</div>